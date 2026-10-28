@extends('layouts.app')

@section('content')
@php $esAdmin = auth()->user()->tieneRol('administrador'); @endphp
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Habitaciones</h1>
        @if ($esAdmin)
            <a href="{{ route('habitaciones.create') }}" class="btn btn-primary">Nueva habitación</a>
        @endif
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-3">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Número de habitación">
        </div>
        <div class="col-6 col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->id }}" @selected(request('tipo') == $tipo->id)>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') == $estado)>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Filtrar</button>
            <a href="{{ route('habitaciones.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Tarifa</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($habitaciones as $habitacion)
                    @php
                        $color = match ($habitacion->estado) {
                            'disponible' => 'success',
                            'ocupada' => 'danger',
                            'limpieza' => 'warning',
                            'mantenimiento' => 'secondary',
                            default => 'light',
                        };
                        $opciones = auth()->user()->tieneRol('limpieza') ? ['limpieza', 'disponible'] : $estados;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $habitacion->numero }}</td>
                        <td>{{ $habitacion->tipo->nombre }}</td>
                        <td>Q {{ number_format($habitacion->tipo->tarifa_noche, 2) }}</td>
                        <td>
                            <span class="badge text-bg-{{ $color }} mb-1">{{ ucfirst($habitacion->estado) }}</span>
                            <form action="{{ route('habitaciones.estado', $habitacion) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="estado" class="form-select form-select-sm" style="max-width: 160px;"
                                        onchange="this.form.submit()" aria-label="Cambiar estado">
                                    @foreach ($opciones as $estado)
                                        <option value="{{ $estado }}" @selected($habitacion->estado === $estado)>{{ ucfirst($estado) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-end">
                            @if ($esAdmin)
                                <a href="{{ route('habitaciones.edit', $habitacion) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('habitaciones.destroy', $habitacion) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar la habitación {{ $habitacion->numero }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No se encontraron habitaciones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $habitaciones->links('pagination::bootstrap-5') }}
</div>
@endsection
