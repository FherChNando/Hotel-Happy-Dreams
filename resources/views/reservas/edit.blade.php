@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 820px;">
    <h1 class="h3 mb-3">Modificar reserva #{{ $reserva->id }}</h1>

    @include('partials.alertas')

    <form action="{{ route('reservas.update', $reserva) }}" method="POST">
        @csrf
        @method('PUT')
        @include('reservas._form')
    </form>
</div>
@endsection
