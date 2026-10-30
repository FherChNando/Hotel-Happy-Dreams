@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nuevo servicio</h1>

    @include('partials.alertas')

    <form action="{{ route('servicios.store') }}" method="POST">
        @csrf
        @include('servicios._form', ['servicio' => null])
    </form>
</div>
@endsection
