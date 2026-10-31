@extends('layouts.app')

@section('content')
<div class="container login-page">
    <div class="login-shell">
        <aside class="login-showcase">
            <div class="login-showcase-top">
                <span class="login-emblem" aria-hidden="true">HD</span>
                <span class="login-showcase-brand">HAPPY DREAMS<br><small>HOTEL &amp; SUITES</small></span>
            </div>

            <div class="login-showcase-message">
                <span class="login-eyebrow">HOSPITALIDAD CON DISTINCIÓN</span>
                <h1>Un lugar para<br>sentirse en casa.</h1>
                <p>Momentos memorables comienzan con una atención extraordinaria.</p>
            </div>

            <div class="login-showcase-footer">
                <span class="login-orbit login-orbit-one"></span>
                <span class="login-orbit login-orbit-two"></span>
                <span class="login-orbit login-orbit-three"></span>
                <span class="login-showcase-caption">BIENVENIDO A HAPPY DREAMS</span>
            </div>
        </aside>

        <section class="login-panel">
            <div class="login-panel-content">
                <div class="login-heading">
                    <span class="login-kicker">ACCESO AL SISTEMA</span>
                    <h2>Qué gusto verte.</h2>
                    <p>Inicia sesión con tu cuenta para continuar.</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                               placeholder="nombre@happydreams.com">

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="login-forgot-link" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary login-submit">
                        <span>{{ __('Login') }}</span>
                        <span class="login-submit-arrow" aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="login-security-note">
                    <span aria-hidden="true">✦</span>
                    Acceso seguro y exclusivo para el equipo del hotel
                </p>
            </div>
        </section>
    </div>

    <p class="login-copyright">© {{ date('Y') }} Happy Dreams Hotel &amp; Suites</p>
</div>
@endsection
