@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h3 mb-3">Nueva empresa</h1>

    @include('partials.alertas')

    <form action="{{ route('empresas.store') }}" method="POST">
        @csrf
        @include('empresas._form', ['empresa' => null])
    </form>
</div>
@endsection
