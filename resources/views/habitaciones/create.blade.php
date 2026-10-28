@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nueva habitación</h1>

    @include('partials.alertas')

    <form action="{{ route('habitaciones.store') }}" method="POST">
        @csrf
        @include('habitaciones._form', ['habitacion' => null])
    </form>
</div>
@endsection
