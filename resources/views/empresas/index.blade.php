@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Empresas con crédito</h1>
        <a href="{{ route('empresas.create') }}" class="btn btn-primary">Nueva empresa</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Buscar por nombre o NIT">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Buscar</button>
            <a href="{{ route('empresas.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>NIT</th>
                    <th>Contacto</th>
                    <th>Límite</th>
                    <th>Saldo pendiente</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empresas as $empresa)
                    @php $saldo = $empresa->saldoPendiente(); @endphp
                    <tr>
                        <td>{{ $empresa->nombre }}</td>
                        <td>{{ $empresa->nit }}</td>
                        <td>
                            {{ $empresa->contacto ?? '—' }}
                            @if ($empresa->telefono)
                                <div class="small text-muted">{{ $empresa->telefono }}</div>
                            @endif
                        </td>
                        <td>Q {{ number_format($empresa->limite_credito, 2) }}</td>
                        <td class="{{ $saldo > $empresa->limite_credito ? 'text-danger fw-semibold' : '' }}">
                            Q {{ number_format($saldo, 2) }}
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $empresa->activo ? 'success' : 'secondary' }}">
                                {{ $empresa->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar a {{ $empresa->nombre }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No se encontraron empresas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $empresas->links('pagination::bootstrap-5') }}
</div>
@endsection
