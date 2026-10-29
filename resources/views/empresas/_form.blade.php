<div class="row">
    <div class="col-md-8 mb-3">
        <label for="nombre" class="form-label">Nombre de la empresa</label>
        <input type="text" id="nombre" name="nombre" maxlength="120" required
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $empresa?->nombre) }}">
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="nit" class="form-label">NIT</label>
        <input type="text" id="nit" name="nit" maxlength="20" required
               class="form-control @error('nit') is-invalid @enderror"
               value="{{ old('nit', $empresa?->nit) }}">
        @error('nit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="contacto" class="form-label">Persona de contacto</label>
        <input type="text" id="contacto" name="contacto" maxlength="120"
               class="form-control @error('contacto') is-invalid @enderror"
               value="{{ old('contacto', $empresa?->contacto) }}">
        @error('contacto') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" id="telefono" name="telefono" maxlength="20"
               class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $empresa?->telefono) }}">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label for="limite_credito" class="form-label">Límite de crédito (Q)</label>
    <input type="number" id="limite_credito" name="limite_credito" min="0" step="0.01" required
           class="form-control @error('limite_credito') is-invalid @enderror"
           value="{{ old('limite_credito', $empresa?->limite_credito ?? 0) }}">
    @error('limite_credito') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-4">
    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1"
           @checked(old('activo', $empresa?->activo ?? true))>
    <label class="form-check-label" for="activo">Empresa activa (puede usar crédito)</label>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
