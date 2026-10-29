@extends('layouts.app')

@section('content')
@php $esAdmin = auth()->user()->tieneRol('administrador'); @endphp
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Huéspedes</h1>
        <a href="{{ route('huespedes.create') }}" class="btn btn-primary">Nuevo huésped</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Buscar por nombre, documento o teléfono">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Buscar</button>
            <a href="{{ route('huespedes.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Reservas</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($huespedes as $huesped)
                    <tr>
                        <td>{{ $huesped->nombre }}</td>
                        <td>{{ $huesped->documento }}</td>
                        <td>{{ $huesped->telefono ?? '—' }}</td>
                        <td>{{ $huesped->email ?? '—' }}</td>
                        <td>{{ $huesped->reservas_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('huespedes.edit', $huesped) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            @if ($esAdmin)
                                <form action="{{ route('huespedes.destroy', $huesped) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar a {{ $huesped->nombre }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No se encontraron huéspedes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $huespedes->links('pagination::bootstrap-5') }}
</div>
@endsection
