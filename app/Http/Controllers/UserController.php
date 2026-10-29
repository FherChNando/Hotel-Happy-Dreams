<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = User::with('rol')
            ->when($request->filled('q'), function ($query) use ($request) {
                $t = '%' . $request->q . '%';
                $query->where(fn ($w) => $w->where('name', 'like', $t)->orWhere('email', 'like', $t));
            })
            ->when($request->filled('rol'), fn ($q) => $q->where('rol_id', $request->rol))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('usuarios.create', ['roles' => Rol::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $request->merge(['activo' => $request->boolean('activo')]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean'],
        ]);

        $usuario = User::create($data); // el cast "hashed" cifra la contraseña
        $this->registrar("Creó el usuario {$usuario->email}");

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => Rol::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $request->merge(['activo' => $request->boolean('activo')]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean'],
        ]);

        // Un administrador no puede quitarse su propio rol ni desactivarse
        if ($usuario->id === auth()->id()) {
            $data['rol_id'] = $usuario->rol_id;
            $data['activo'] = true;
        }

        // Contraseña vacía = no cambiarla
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);
        $this->registrar("Actualizó el usuario {$usuario->email}");

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function cambiarActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);
        $this->registrar(($usuario->activo ? 'Activó' : 'Desactivó') . " al usuario {$usuario->email}");

        return back()->with('success', 'Estado del usuario actualizado.');
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
