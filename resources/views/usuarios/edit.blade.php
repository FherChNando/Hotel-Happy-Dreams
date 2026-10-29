@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar usuario</h1>

    @include('partials.alertas')

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')
        @include('usuarios._form')
    </form>
</div>
@endsection
