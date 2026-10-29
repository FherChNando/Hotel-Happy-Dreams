@php $esYo = isset($usuario) && $usuario && $usuario->id === auth()->id(); @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" id="name" name="name" maxlength="100" required
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $usuario?->name) }}">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="120" required
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $usuario?->email) }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">
            Contraseña @if ($usuario) <span class="text-muted small">(dejar vacío para no cambiarla)</span> @endif
        </label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password"
               @required(! $usuario)
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
               autocomplete="new-password" class="form-control">
    </div>
</div>

<div class="mb-3">
    <label for="rol_id" class="form-label">Rol</label>
    <select id="rol_id" name="rol_id" required @disabled($esYo)
            class="form-select @error('rol_id') is-invalid @enderror">
        <option value="">Seleccione...</option>
        @foreach ($roles as $rol)
            <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario?->rol_id) == $rol->id)>{{ ucfirst($rol->nombre) }}</option>
        @endforeach
    </select>
    @if ($esYo)
        <input type="hidden" name="rol_id" value="{{ $usuario->rol_id }}">
        <div class="form-text">No puedes cambiar tu propio rol.</div>
    @endif
    @error('rol_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-4">
    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1"
           @checked(old('activo', $usuario?->activo ?? true)) @disabled($esYo)>
    <label class="form-check-label" for="activo">Usuario activo (puede iniciar sesión)</label>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
