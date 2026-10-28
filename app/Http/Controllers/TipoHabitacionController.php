<?php

namespace App\Http\Controllers;

use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoHabitacionController extends Controller
{
    public function index(Request $request)
    {
        $tipos = TipoHabitacion::withCount('habitaciones')
            ->when($request->filled('q'), fn ($q) => $q->where('nombre', 'like', '%' . $request->q . '%'))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('tipos_habitacion.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos_habitacion.create');
    }

    public function store(Request $request)
    {
        TipoHabitacion::create($this->validar($request));

        return redirect()->route('tipos-habitacion.index')
            ->with('success', 'Tipo de habitación creado correctamente.');
    }

    public function edit(TipoHabitacion $tipo)
    {
        return view('tipos_habitacion.edit', compact('tipo'));
    }

    public function update(Request $request, TipoHabitacion $tipo)
    {
        $tipo->update($this->validar($request, $tipo));

        return redirect()->route('tipos-habitacion.index')
            ->with('success', 'Tipo de habitación actualizado correctamente.');
    }

    public function destroy(TipoHabitacion $tipo)
    {
        if ($tipo->habitaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay habitaciones de este tipo.');
        }

        $tipo->delete();

        return redirect()->route('tipos-habitacion.index')
            ->with('success', 'Tipo de habitación eliminado.');
    }

    private function validar(Request $request, ?TipoHabitacion $tipo = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('tipos_habitacion', 'nombre')->ignore($tipo?->id)],
            'capacidad' => ['required', 'integer', 'min:1', 'max:10'],
            'tarifa_noche' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ]);
    }
}
