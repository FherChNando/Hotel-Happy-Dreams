@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar servicio</h1>

    @include('partials.alertas')

    <form action="{{ route('servicios.update', $servicio) }}" method="POST">
        @csrf
        @method('PUT')
        @include('servicios._form')
    </form>
</div>
@endsection
