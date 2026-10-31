@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $esAdmin = $user->tieneRol('administrador');
    $esRecep = $user->tieneRol('recepcionista');
@endphp
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Panel principal</h1>
            <p class="text-muted mb-0">Bienvenido, {{ $user->name }} ({{ ucfirst($user->rol?->nombre ?? 'usuario') }})</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('habitaciones.index') }}" class="btn btn-outline-primary">Ver habitaciones</a>
            @if (($esAdmin || $esRecep) && Route::has('reservas.create'))
                <a href="{{ route('reservas.create') }}" class="btn btn-primary">Nueva reserva</a>
            @endif
        </div>
    </div>

    @include('partials.alertas')

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="text-muted small">Disponibles</div>
                    <div class="fs-2 fw-bold text-success">{{ $disponibles }}</div>
                    <a href="{{ route('habitaciones.index', ['estado' => 'disponible']) }}" class="small text-decoration-none">Ver listado</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-danger h-100">
                <div class="card-body">
                    <div class="text-muted small">Ocupadas</div>
                    <div class="fs-2 fw-bold text-danger">{{ $ocupadas }}</div>
                    <a href="{{ route('habitaciones.index', ['estado' => 'ocupada']) }}" class="small text-decoration-none">Ver listado</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small">En limpieza</div>
                    <div class="fs-2 fw-bold text-warning">{{ $limpieza }}</div>
                    <a href="{{ route('habitaciones.index', ['estado' => 'limpieza']) }}" class="small text-decoration-none">Ver listado</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-secondary h-100">
                <div class="card-body">
                    <div class="text-muted small">Mantenimiento</div>
                    <div class="fs-2 fw-bold text-secondary">{{ $mantenimiento }}</div>
                    <a href="{{ route('habitaciones.index', ['estado' => 'mantenimiento']) }}" class="small text-decoration-none">Ver listado</a>
                </div>
            </div>
        </div>
    </div>

    @if ($esAdmin || $esRecep)
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Entradas para hoy</div>
                        <div class="fs-4 fw-semibold">{{ $entradasHoy }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Salidas para hoy</div>
                        <div class="fs-4 fw-semibold">{{ $salidasHoy }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Huéspedes registrados</div>
                        <div class="fs-4 fw-semibold">{{ $totalHuespedes }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Empresas activas</div>
                        <div class="fs-4 fw-semibold">{{ $empresasActivas }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Próximas reservas</span>
                        @if (Route::has('reservas.index'))
                            <a href="{{ route('reservas.index') }}" class="small">Ver todas</a>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Huésped</th>
                                    <th>Habitación</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proximasEntradas as $reserva)
                                    <tr>
                                        <td>{{ $reserva->huesped->nombre }}</td>
                                        <td>{{ $reserva->habitacion?->numero ?? '—' }}</td>
                                        <td>{{ $reserva->fecha_entrada->format('d/m/Y') }}</td>
                                        <td>{{ $reserva->fecha_salida->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin reservas próximas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">Huéspedes hospedados actualmente</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Habitación</th>
                                    <th>Huésped</th>
                                    <th>Salida prevista</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ocupadasHoy as $reserva)
                                    <tr>
                                        <td class="fw-semibold">{{ $reserva->habitacion?->numero ?? '—' }}</td>
                                        <td>{{ $reserva->huesped->nombre }}</td>
                                        <td>{{ $reserva->fecha_salida->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No hay estadías en curso.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($esAdmin)
        <div class="card">
            <div class="card-header">Actividad reciente (Bitácora)</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bitacora as $item)
                            <tr>
                                <td class="text-muted">{{ $item->fecha->format('d/m/Y H:i') }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->accion }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin actividad registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
