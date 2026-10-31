@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Reservas</h1>
        <a href="{{ route('reservas.create') }}" class="btn btn-primary">Nueva reserva</a>
    </div>

    @include('partials.alertas')

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-md-3">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Huésped o documento">
        </div>
        <div class="col-6 col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <input type="date" name="desde" value="{{ request('desde') }}" class="form-control" aria-label="Desde">
        </div>
        <div class="col-6 col-md-2">
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control" aria-label="Hasta">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Filtrar</button>
            <a href="{{ route('reservas.index') }}" class="btn btn-link">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservas as $reserva)
                    @php
                        $color = match ($reserva->estado) {
                            'reservada' => 'primary',
                            'check_in' => 'success',
                            'check_out' => 'secondary',
                            'cancelada' => 'danger',
                            default => 'light',
                        };
                    @endphp
                    <tr>
                        <td>{{ $reserva->id }}</td>
                        <td>{{ $reserva->huesped->nombre }}</td>
                        <td>{{ $reserva->habitacion?->numero ?? '—' }} <span class="small text-muted">{{ $reserva->habitacion?->tipo->nombre }}</span></td>
                        <td>{{ $reserva->fecha_entrada->format('d/m/Y') }}</td>
                        <td>{{ $reserva->fecha_salida->format('d/m/Y') }}</td>
                        <td>
                            {{ ucfirst($reserva->forma_pago) }}
                            @if ($reserva->empresa)
                                <div class="small text-muted">{{ $reserva->empresa->nombre }}</div>
                            @endif
                        </td>
                        <td>Q {{ number_format($reserva->total(), 2) }}</td>
                        <td><span class="badge text-bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $reserva->estado)) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('reservas.show', $reserva) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            @if ($reserva->estado === 'reservada')
                                <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Cancelar la reserva #{{ $reserva->id }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">No se encontraron reservas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reservas->links('pagination::bootstrap-5') }}
</div>
@endsection
