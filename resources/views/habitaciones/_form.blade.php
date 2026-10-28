<div class="mb-3">
    <label for="numero" class="form-label">Número de habitación</label>
    <input type="text" id="numero" name="numero" maxlength="10" required
           class="form-control @error('numero') is-invalid @enderror"
           value="{{ old('numero', $habitacion?->numero) }}">
    @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="tipo_habitacion_id" class="form-label">Tipo</label>
    <select id="tipo_habitacion_id" name="tipo_habitacion_id" required
            class="form-select @error('tipo_habitacion_id') is-invalid @enderror">
        <option value="">Seleccione...</option>
        @foreach ($tipos as $tipo)
            <option value="{{ $tipo->id }}" @selected(old('tipo_habitacion_id', $habitacion?->tipo_habitacion_id) == $tipo->id)>
                {{ $tipo->nombre }} (Q {{ number_format($tipo->tarifa_noche, 2) }})
            </option>
        @endforeach
    </select>
    @error('tipo_habitacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="estado" class="form-label">Estado</label>
    <select id="estado" name="estado" required class="form-select @error('estado') is-invalid @enderror">
        @foreach ($estados as $estado)
            <option value="{{ $estado }}" @selected(old('estado', $habitacion?->estado ?? 'disponible') === $estado)>{{ ucfirst($estado) }}</option>
        @endforeach
    </select>
    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('habitaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
