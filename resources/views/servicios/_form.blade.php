<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="80" required
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $servicio?->nombre) }}">
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="categoria" class="form-label">Categoría</label>
        <select id="categoria" name="categoria" required
                class="form-select @error('categoria') is-invalid @enderror">
            <option value="">Seleccione...</option>
            @foreach ($categorias as $clave => $etiqueta)
                <option value="{{ $clave }}" @selected(old('categoria', $servicio?->categoria) === $clave)>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="precio" class="form-label">Precio (Q)</label>
        <input type="number" id="precio" name="precio" min="0" step="0.01" required
               class="form-control @error('precio') is-invalid @enderror"
               value="{{ old('precio', $servicio?->precio) }}">
        @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
