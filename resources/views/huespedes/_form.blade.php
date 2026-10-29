<div class="mb-3">
    <label for="nombre" class="form-label">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" maxlength="120" required
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $huesped?->nombre) }}">
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="documento" class="form-label">Documento (DPI o pasaporte)</label>
    <input type="text" id="documento" name="documento" maxlength="30" required
           class="form-control @error('documento') is-invalid @enderror"
           value="{{ old('documento', $huesped?->documento) }}">
    @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" id="telefono" name="telefono" maxlength="20"
               class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $huesped?->telefono) }}">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="120"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $huesped?->email) }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('huespedes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
