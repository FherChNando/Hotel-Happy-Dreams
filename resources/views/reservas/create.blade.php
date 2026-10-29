@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 820px;">
    <h1 class="h3 mb-3">Nueva reserva</h1>

    @include('partials.alertas')

    <form action="{{ route('reservas.store') }}" method="POST">
        @csrf
        @include('reservas._form', ['reserva' => null])
    </form>
</div>
@endsection
