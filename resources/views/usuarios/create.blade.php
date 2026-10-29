@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nuevo usuario</h1>

    @include('partials.alertas')

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        @include('usuarios._form', ['usuario' => null])
    </form>
</div>
@endsection
