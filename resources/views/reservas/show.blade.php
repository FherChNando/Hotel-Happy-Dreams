@extends('layouts.app')

@section('content')
@php
    $color = match ($reserva->estado) {
        'reservada' => 'primary',
        'check_in' => 'success',
        'check_out' => 'secondary',
        'cancelada' => 'danger',
        default => 'light',
    };
    $tarifa = $reserva->habitacion?->tipo?->tarifa_noche ?? 0;
@endphp
<div class="container" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">
            Reserva #{{ $reserva->id }}
            <span class="badge text-bg-{{ $color }} fs-6">{{ ucfirst(str_replace('_', ' ', $reserva->estado)) }}</span>
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary">Volver</a>
            @if ($reserva->estado === 'reservada')
                <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-outline-primary">Modificar</a>
                <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST"
                      onsubmit="return confirm('¿Cancelar esta reserva?')">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-danger">Cancelar reserva</button>
                </form>
            @endif
        </div>
    </div>

    @include('partials.alertas')

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Huésped</div>
                <div class="card-body">
                    <p class="mb-1 fw-semibold">{{ $reserva->huesped->nombre }}</p>
                    <p class="mb-1">Documento: {{ $reserva->huesped->documento }}</p>
                    <p class="mb-1">Teléfono: {{ $reserva->huesped->telefono ?? '—' }}</p>
                    <p class="mb-0">Correo: {{ $reserva->huesped->email ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Estadía</div>
                <div class="card-body">
                    <p class="mb-1">Habitación: <strong>{{ $reserva->habitacion?->numero ?? 'Sin asignar' }}</strong> ({{ $reserva->habitacion?->tipo?->nombre }})</p>
                    <p class="mb-1">Entrada: {{ $reserva->fecha_entrada->format('d/m/Y') }}</p>
                    <p class="mb-1">Salida: {{ $reserva->fecha_salida->format('d/m/Y') }} ({{ $reserva->noches() }} noche(s))</p>
                    <p class="mb-1">Forma de pago: {{ ucfirst($reserva->forma_pago) }}</p>
                    @if ($reserva->empresa)
                        <p class="mb-1">Empresa: {{ $reserva->empresa->nombre }}</p>
                    @endif
                    <p class="mb-0 small text-muted">Registrada por {{ $reserva->user->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Cuenta</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <tbody>
                    <tr>
                        <td>Hospedaje ({{ $reserva->noches() }} noche(s) × Q {{ number_format($tarifa, 2) }}, desayuno incluido)</td>
                        <td class="text-end">Q {{ number_format($reserva->totalHospedaje(), 2) }}</td>
                    </tr>
                    @foreach ($reserva->consumos as $consumo)
                        <tr>
                            <td>{{ $consumo->servicio->nombre }} × {{ $consumo->cantidad }}</td>
                            <td class="text-end">Q {{ number_format($consumo->cantidad * $consumo->precio_unitario, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-semibold">
                        <td>Total</td>
                        <td class="text-end">Q {{ number_format($reserva->total(), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Pagado</td>
                        <td class="text-end">Q {{ number_format($reserva->totalPagado(), 2) }}</td>
                    </tr>
                    <tr class="fw-semibold">
                        <td>Saldo pendiente</td>
                        <td class="text-end">Q {{ number_format($reserva->saldo(), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
