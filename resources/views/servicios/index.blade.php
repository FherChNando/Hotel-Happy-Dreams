@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Servicios</h1>
        <a href="{{ route('servicios.create') }}" class="btn btn-primary">Nuevo servicio</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-5">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre">
        </div>
        <div class="col-12 col-md-4">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(request('categoria') === $clave)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Filtrar</button>
            <a href="{{ route('servicios.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Consumos</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($servicios as $servicio)
                    <tr>
                        <td class="fw-semibold">{{ $servicio->nombre }}</td>
                        <td>
                            <span class="badge text-bg-secondary">{{ $categorias[$servicio->categoria] ?? ucfirst($servicio->categoria) }}</span>
                        </td>
                        <td>Q {{ number_format($servicio->precio, 2) }}</td>
                        <td>{{ $servicio->consumos_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('servicios.destroy', $servicio) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar el servicio {{ $servicio->nombre }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No hay servicios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $servicios->links('pagination::bootstrap-5') }}
</div>
@endsection
