<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $huespedes = Huesped::withCount('reservas')
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w->where('nombre', 'like', $t)
                    ->orWhere('documento', 'like', $t)
                    ->orWhere('telefono', 'like', $t));
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('huespedes.index', compact('huespedes'));
    }

    public function create()
    {
        return view('huespedes.create');
    }

    public function store(Request $request)
    {
        Huesped::create($this->validar($request));

        return redirect()->route('huespedes.index')
            ->with('success', 'Huésped registrado correctamente.');
    }

    public function edit(Huesped $huesped)
    {
        return view('huespedes.edit', compact('huesped'));
    }

    public function update(Request $request, Huesped $huesped)
    {
        $huesped->update($this->validar($request, $huesped));

        return redirect()->route('huespedes.index')
            ->with('success', 'Huésped actualizado correctamente.');
    }

    public function destroy(Huesped $huesped)
    {
        if ($huesped->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar: el huésped tiene reservas registradas.');
        }

        $huesped->delete();

        return redirect()->route('huespedes.index')->with('success', 'Huésped eliminado.');
    }

    private function validar(Request $request, ?Huesped $huesped = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'documento' => ['required', 'string', 'max:30', Rule::unique('huespedes', 'documento')->ignore($huesped?->id)],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);
    }
}
