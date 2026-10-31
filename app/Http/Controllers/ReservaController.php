<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public const ESTADOS = ['reservada', 'check_in', 'check_out', 'cancelada'];
    public const FORMAS_PAGO = ['efectivo', 'transferencia', 'credito'];

    public function index(Request $request)
    {
        $reservas = Reserva::with(['huesped', 'habitacion.tipo', 'empresa', 'consumos'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->whereHas('huesped', fn ($h) => $h->where('nombre', 'like', $t)->orWhere('documento', 'like', $t));
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha_salida', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha_entrada', '<=', $request->hasta))
            ->orderByDesc('fecha_entrada')
            ->paginate(10)
            ->withQueryString();

        return view('reservas.index', [
            'reservas' => $reservas,
            'estados' => self::ESTADOS,
        ]);
    }

    public function create()
    {
        return view('reservas.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $data = $this->validar($request, true);

        $reserva = DB::transaction(function () use ($data) {
            // Bloquea la fila de la habitación para evitar dobles reservas simultáneas
            $habitacion = Habitacion::with('tipo')->lockForUpdate()->findOrFail($data['habitacion_id']);

            $this->asegurarDisponible($habitacion, $data['fecha_entrada'], $data['fecha_salida']);
            $this->validarCredito($data, $this->estimarTotal($habitacion, $data));

            return Reserva::create($data + ['user_id' => auth()->id(), 'estado' => 'reservada']);
        });

        $this->registrar("Creó la reserva #{$reserva->id}");

        return redirect()->route('reservas.show', $reserva)->with('success', 'Reserva creada correctamente.');
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['huesped', 'habitacion.tipo', 'empresa', 'user', 'consumos.servicio', 'pagos']);

        return view('reservas.show', compact('reserva'));
    }

    public function edit(Reserva $reserva)
    {
        if ($reserva->estado !== 'reservada') {
            return redirect()->route('reservas.show', $reserva)
                ->with('error', 'Solo se pueden modificar reservas en estado "reservada".');
        }

        return view('reservas.edit', $this->datosFormulario() + ['reserva' => $reserva->load('huesped')]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        if ($reserva->estado !== 'reservada') {
            return redirect()->route('reservas.show', $reserva)
                ->with('error', 'Solo se pueden modificar reservas en estado "reservada".');
        }

        $data = $this->validar($request, false);

        DB::transaction(function () use ($data, $reserva) {
            $habitacion = Habitacion::with('tipo')->lockForUpdate()->findOrFail($data['habitacion_id']);

            $this->asegurarDisponible($habitacion, $data['fecha_entrada'], $data['fecha_salida'], $reserva->id);
            $this->validarCredito($data, $this->estimarTotal($habitacion, $data), $reserva);

            $reserva->update($data);
        });

        $this->registrar("Modificó la reserva #{$reserva->id}");

        return redirect()->route('reservas.show', $reserva)->with('success', 'Reserva actualizada correctamente.');
    }

    public function cancelar(Reserva $reserva)
    {
        if ($reserva->estado !== 'reservada') {
            return back()->with('error', 'Solo se pueden cancelar reservas en estado "reservada".');
        }

        $reserva->update(['estado' => 'cancelada']);
        $this->registrar("Canceló la reserva #{$reserva->id}");

        return back()->with('success', 'Reserva cancelada.');
    }

    /** AJAX: habitaciones libres para un rango de fechas */
    public function disponibilidad(Request $request)
    {
        $data = $request->validate([
            'entrada' => ['required', 'date'],
            'salida' => ['required', 'date', 'after:entrada'],
            'tipo' => ['nullable', 'exists:tipos_habitacion,id'],
            'excluir' => ['nullable', 'integer'],
        ]);

        $habitaciones = Habitacion::with('tipo')
            ->where('estado', '!=', 'mantenimiento')
            ->when($data['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo_habitacion_id', $tipo))
            ->whereDoesntHave('reservas', function ($q) use ($data) {
                $q->whereIn('estado', ['reservada', 'check_in'])
                  ->where('fecha_entrada', '<', $data['salida'])
                  ->where('fecha_salida', '>', $data['entrada'])
                  ->when($data['excluir'] ?? null, fn ($r, $id) => $r->where('id', '!=', $id));
            })
            ->orderBy('numero')
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'numero' => $h->numero,
                'tipo' => $h->tipo->nombre,
                'capacidad' => $h->tipo->capacidad,
                'tarifa' => (float) $h->tipo->tarifa_noche,
            ]);

        return response()->json([
            'noches' => max(1, (int) Carbon::parse($data['entrada'])->diffInDays(Carbon::parse($data['salida']))),
            'habitaciones' => $habitaciones,
        ]);
    }

    // ---------------------------------------------------------------

    private function datosFormulario(): array
    {
        return [
            'huespedes' => Huesped::orderBy('nombre')->get(['id', 'nombre', 'documento']),
            'tipos' => TipoHabitacion::orderBy('nombre')->get(),
            'empresas' => Empresa::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'formasPago' => self::FORMAS_PAGO,
        ];
    }

    private function validar(Request $request, bool $esNueva): array
    {
        $reglas = [
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'fecha_entrada' => ['required', 'date'],
            'fecha_salida' => ['required', 'date', 'after:fecha_entrada'],
            'forma_pago' => ['required', Rule::in(self::FORMAS_PAGO)],
            'empresa_id' => ['nullable', 'required_if:forma_pago,credito', 'exists:empresas,id'],
        ];

        if ($esNueva) {
            $reglas['huesped_id'] = ['required', 'exists:huespedes,id'];
            $reglas['fecha_entrada'][] = 'after_or_equal:today';
        }

        $data = $request->validate($reglas);

        if ($data['forma_pago'] !== 'credito') {
            $data['empresa_id'] = null;
        }

        return $data;
    }

    private function asegurarDisponible(Habitacion $habitacion, string $entrada, string $salida, ?int $excluirId = null): void
    {
        $ocupada = $habitacion->estado === 'mantenimiento'
            || $habitacion->reservas()
                ->whereIn('estado', ['reservada', 'check_in'])
                ->where('fecha_entrada', '<', $salida)
                ->where('fecha_salida', '>', $entrada)
                ->when($excluirId, fn ($q) => $q->where('id', '!=', $excluirId))
                ->exists();

        if ($ocupada) {
            throw ValidationException::withMessages([
                'habitacion_id' => 'La habitación ya no está disponible en esas fechas. Elige otra.',
            ]);
        }
    }

    private function estimarTotal(Habitacion $habitacion, array $data): float
    {
        $noches = max(1, (int) Carbon::parse($data['fecha_entrada'])->diffInDays(Carbon::parse($data['fecha_salida'])));

        return $noches * (float) $habitacion->tipo->tarifa_noche;
    }

    private function validarCredito(array $data, float $total, ?Reserva $actual = null): void
    {
        if ($data['forma_pago'] !== 'credito') {
            return;
        }

        $empresa = Empresa::findOrFail($data['empresa_id']);

        if (! $empresa->activo) {
            throw ValidationException::withMessages(['empresa_id' => 'La empresa está inactiva.']);
        }

        $pendiente = $empresa->saldoPendiente();

        // Al modificar, no contar dos veces la reserva actual
        if ($actual && $actual->forma_pago === 'credito' && $actual->empresa_id === $empresa->id) {
            $pendiente -= $actual->saldo();
        }

        if ($pendiente + $total > (float) $empresa->limite_credito) {
            $disponible = max(0, (float) $empresa->limite_credito - $pendiente);

            throw ValidationException::withMessages([
                'empresa_id' => 'La reserva excede el crédito de la empresa. Crédito disponible: Q ' . number_format($disponible, 2) . '.',
            ]);
        }
    }

    private function registrar(string $accion): void
    {
        Bitacora::create(['user_id' => auth()->id(), 'accion' => $accion, 'fecha' => now()]);
    }
}
