@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nuevo tipo de habitación</h1>

    @include('partials.alertas')

    <form action="{{ route('tipos-habitacion.store') }}" method="POST">
        @csrf
        @include('tipos_habitacion._form', ['tipo' => null])
    </form>
</div>
@endsection
