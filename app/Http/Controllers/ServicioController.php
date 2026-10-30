<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    public const CATEGORIAS = [
        'almuerzo' => 'Almuerzo',
        'cena' => 'Cena',
        'room_service' => 'Servicio a habitación',
        'lavanderia' => 'Lavandería',
    ];

    public function index(Request $request)
    {
        $servicios = Servicio::withCount('consumos')
            ->when($request->filled('q'), fn ($q) => $q->where('nombre', 'like', '%' . $request->q . '%'))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->categoria))
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('servicios.index', [
            'servicios' => $servicios,
            'categorias' => self::CATEGORIAS,
        ]);
    }

    public function create()
    {
        return view('servicios.create', [
            'categorias' => self::CATEGORIAS,
        ]);
    }

    public function store(Request $request)
    {
        $servicio = Servicio::create($this->validar($request));
        $this->registrar("Creó el servicio {$servicio->nombre}");

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio registrado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.edit', [
            'servicio' => $servicio,
            'categorias' => self::CATEGORIAS,
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $servicio->update($this->validar($request));
        $this->registrar("Actualizó el servicio {$servicio->nombre}");

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        if ($servicio->consumos()->exists()) {
            return back()->with('error', 'No se puede eliminar: el servicio ya tiene consumos registrados.');
        }

        $nombre = $servicio->nombre;
        $servicio->delete();
        $this->registrar("Eliminó el servicio {$nombre}");

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'categoria' => ['required', Rule::in(array_keys(self::CATEGORIAS))],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ]);
    }

    private function registrar(string $accion): void
    {
        Bitacora::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'fecha' => now(),
        ]);
    }
}
