@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Usuarios</h1>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Nuevo usuario</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Buscar por nombre o correo">
        </div>
        <div class="col-12 col-md-3">
            <select name="rol" class="form-select">
                <option value="">Todos los roles</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id }}" @selected(request('rol') == $rol->id)>{{ ucfirst($rol->nombre) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Filtrar</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    @php $esYo = $usuario->id === auth()->id(); @endphp
                    <tr>
                        <td>{{ $usuario->name }} @if ($esYo) <span class="text-muted small">(tú)</span> @endif</td>
                        <td>{{ $usuario->email }}</td>
                        <td><span class="badge text-bg-info">{{ ucfirst($usuario->rol?->nombre ?? 'sin rol') }}</span></td>
                        <td>
                            <span class="badge text-bg-{{ $usuario->activo ? 'success' : 'secondary' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('usuarios.activo', $usuario) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿{{ $usuario->activo ? 'Desactivar' : 'Activar' }} a {{ $usuario->name }}?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-{{ $usuario->activo ? 'danger' : 'success' }}" @disabled($esYo)>
                                    {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No se encontraron usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $usuarios->links('pagination::bootstrap-5') }}
</div>
@endsection
