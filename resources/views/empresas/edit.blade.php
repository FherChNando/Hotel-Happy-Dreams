@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Editar empresa</h1>

    @include('partials.alertas')

    <form action="{{ route('empresas.update', $empresa) }}" method="POST">
        @csrf
        @method('PUT')
        @include('empresas._form')
    </form>
</div>
@endsection
