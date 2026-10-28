@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar habitación {{ $habitacion->numero }}</h1>

    @include('partials.alertas')

    <form action="{{ route('habitaciones.update', $habitacion) }}" method="POST">
        @csrf
        @method('PUT')
        @include('habitaciones._form')
    </form>
</div>
@endsection
