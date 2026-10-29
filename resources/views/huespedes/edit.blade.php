@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar huésped</h1>

    @include('partials.alertas')

    <form action="{{ route('huespedes.update', $huesped) }}" method="POST">
        @csrf
        @method('PUT')
        @include('huespedes._form')
    </form>
</div>
@endsection
