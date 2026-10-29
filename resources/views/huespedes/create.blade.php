@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nuevo huésped</h1>

    @include('partials.alertas')

    <form action="{{ route('huespedes.store') }}" method="POST">
        @csrf
        @include('huespedes._form', ['huesped' => null])
    </form>
</div>
@endsection
