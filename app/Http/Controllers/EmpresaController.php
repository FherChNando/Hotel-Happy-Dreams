<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $empresas = Empresa::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w->where('nombre', 'like', $t)->orWhere('nit', 'like', $t));
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        Empresa::create($this->validar($request));

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa registrada correctamente.');
    }

    public function edit(Empresa $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $empresa->update($this->validar($request, $empresa));

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa actualizada correctamente.');
    }

    public function destroy(Empresa $empresa)
    {
        if ($empresa->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar: la empresa tiene reservas. Desactívela en su lugar.');
        }

        $empresa->delete();

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada.');
    }

    private function validar(Request $request, ?Empresa $empresa = null): array
    {
        // El checkbox no envía valor cuando está desmarcado
        $request->merge(['activo' => $request->boolean('activo')]);

        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'nit' => ['required', 'string', 'max:20', Rule::unique('empresas', 'nit')->ignore($empresa?->id)],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'limite_credito' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'activo' => ['boolean'],
        ]);
    }
}
