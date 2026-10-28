<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="50" required
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $tipo?->nombre) }}">
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="capacidad" class="form-label">Capacidad (personas)</label>
        <input type="number" id="capacidad" name="capacidad" min="1" max="10" required
               class="form-control @error('capacidad') is-invalid @enderror"
               value="{{ old('capacidad', $tipo?->capacidad) }}">
        @error('capacidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="tarifa_noche" class="form-label">Tarifa por noche (Q)</label>
        <input type="number" id="tarifa_noche" name="tarifa_noche" min="0" step="0.01" required
               class="form-control @error('tarifa_noche') is-invalid @enderror"
               value="{{ old('tarifa_noche', $tipo?->tarifa_noche) }}">
        @error('tarifa_noche') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('tipos-habitacion.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
