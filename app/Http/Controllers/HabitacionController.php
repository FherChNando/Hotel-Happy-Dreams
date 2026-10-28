<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public const ESTADOS = ['disponible', 'ocupada', 'limpieza', 'mantenimiento'];

    public function index(Request $request)
    {
        $habitaciones = Habitacion::with('tipo')
            ->when($request->filled('q'), fn ($q) => $q->where('numero', 'like', '%' . $request->q . '%'))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo_habitacion_id', $request->tipo))
            ->orderBy('numero')
            ->paginate(10)
            ->withQueryString();

        return view('habitaciones.index', [
            'habitaciones' => $habitaciones,
            'tipos' => TipoHabitacion::orderBy('nombre')->get(),
            'estados' => self::ESTADOS,
        ]);
    }

    public function create()
    {
        return view('habitaciones.create', [
            'tipos' => TipoHabitacion::orderBy('nombre')->get(),
            'estados' => self::ESTADOS,
        ]);
    }

    public function store(Request $request)
    {
        Habitacion::create($this->validar($request));

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación creada correctamente.');
    }

    public function edit(Habitacion $habitacion)
    {
        return view('habitaciones.edit', [
            'habitacion' => $habitacion,
            'tipos' => TipoHabitacion::orderBy('nombre')->get(),
            'estados' => self::ESTADOS,
        ]);
    }

    public function update(Request $request, Habitacion $habitacion)
    {
        $habitacion->update($this->validar($request, $habitacion));

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación actualizada correctamente.');
    }

    public function destroy(Habitacion $habitacion)
    {
        if ($habitacion->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar: la habitación tiene reservas. Póngala en mantenimiento.');
        }

        $habitacion->delete();

        return redirect()->route('habitaciones.index')
            ->with('success', 'Habitación eliminada.');
    }

    /** Cambio rápido de estado (administrador, recepcionista y limpieza) */
    public function cambiarEstado(Request $request, Habitacion $habitacion)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS)],
        ]);

        // Limpieza solo puede marcar limpieza o disponible
        if ($request->user()->tieneRol('limpieza') && ! in_array($data['estado'], ['limpieza', 'disponible'], true)) {
            abort(403, 'No tienes permiso para este cambio de estado.');
        }

        $habitacion->update($data);

        return back()->with('success', "Habitación {$habitacion->numero} actualizada a {$data['estado']}.");
    }

    private function validar(Request $request, ?Habitacion $habitacion = null): array
    {
        return $request->validate([
            'tipo_habitacion_id' => ['required', 'exists:tipos_habitacion,id'],
            'numero' => ['required', 'string', 'max:10', Rule::unique('habitaciones', 'numero')->ignore($habitacion?->id)],
            'estado' => ['required', Rule::in(self::ESTADOS)],
        ]);
    }
}
