@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar tipo de habitación</h1>

    @include('partials.alertas')

    <form action="{{ route('tipos-habitacion.update', $tipo) }}" method="POST">
        @csrf
        @method('PUT')
        @include('tipos_habitacion._form')
    </form>
</div>
@endsection
