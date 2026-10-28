@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Tipos de habitación</h1>
        <a href="{{ route('tipos-habitacion.create') }}" class="btn btn-primary">Nuevo tipo</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Buscar</button>
            <a href="{{ route('tipos-habitacion.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Capacidad</th>
                    <th>Tarifa por noche</th>
                    <th>Habitaciones</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tipos as $tipo)
                    <tr>
                        <td>{{ $tipo->nombre }}</td>
                        <td>{{ $tipo->capacidad }} persona(s)</td>
                        <td>Q {{ number_format($tipo->tarifa_noche, 2) }}</td>
                        <td>{{ $tipo->habitaciones_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('tipos-habitacion.edit', $tipo) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('tipos-habitacion.destroy', $tipo) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este tipo de habitación?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No hay tipos de habitación registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tipos->links('pagination::bootstrap-5') }}
</div>
@endsection
