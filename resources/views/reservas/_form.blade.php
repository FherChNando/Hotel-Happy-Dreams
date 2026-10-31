@php
    $entradaVal = old('fecha_entrada', $reserva?->fecha_entrada?->format('Y-m-d'));
    $salidaVal = old('fecha_salida', $reserva?->fecha_salida?->format('Y-m-d'));
    $pagoVal = old('forma_pago', $reserva?->forma_pago ?? 'efectivo');
@endphp

{{-- Huésped --}}
<div class="mb-3">
    <label for="huesped_id" class="form-label">Huésped</label>
    @if ($reserva)
        <input type="text" class="form-control" value="{{ $reserva->huesped->nombre }} — {{ $reserva->huesped->documento }}" disabled>
    @else
        <select id="huesped_id" name="huesped_id" required class="form-select @error('huesped_id') is-invalid @enderror">
            <option value="">Seleccione...</option>
            @foreach ($huespedes as $huesped)
                <option value="{{ $huesped->id }}" @selected(old('huesped_id') == $huesped->id)>{{ $huesped->nombre }} — {{ $huesped->documento }}</option>
            @endforeach
        </select>
        @error('huesped_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">¿No está registrado? <a href="{{ route('huespedes.create') }}" target="_blank">Crear huésped</a> y recarga esta página.</div>
    @endif
</div>

{{-- Fechas --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="fecha_entrada" class="form-label">Fecha de entrada</label>
        <input type="date" id="fecha_entrada" name="fecha_entrada" required value="{{ $entradaVal }}"
               @if (! $reserva) min="{{ now()->toDateString() }}" @endif
               class="form-control @error('fecha_entrada') is-invalid @enderror">
        @error('fecha_entrada') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="fecha_salida" class="form-label">Fecha de salida</label>
        <input type="date" id="fecha_salida" name="fecha_salida" required value="{{ $salidaVal }}"
               class="form-control @error('fecha_salida') is-invalid @enderror">
        @error('fecha_salida') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Disponibilidad (AJAX) --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="tipo_filtro" class="form-label">Tipo de habitación</label>
        <select id="tipo_filtro" class="form-select">
            <option value="">Todos</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8 mb-3">
        <label for="habitacion_id" class="form-label">Habitación disponible</label>
        <select id="habitacion_id" name="habitacion_id" required data-selected="{{ old('habitacion_id', $reserva?->habitacion_id) }}"
                class="form-select @error('habitacion_id') is-invalid @enderror">
            <option value="">Seleccione las fechas primero...</option>
        </select>
        @error('habitacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div id="info_disponibilidad" class="form-text" aria-live="polite"></div>
    </div>
</div>

<div id="resumen" class="alert alert-info py-2 d-none"></div>

{{-- Forma de pago --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="forma_pago" class="form-label">Forma de pago</label>
        <select id="forma_pago" name="forma_pago" required class="form-select @error('forma_pago') is-invalid @enderror">
            @foreach ($formasPago as $forma)
                <option value="{{ $forma }}" @selected($pagoVal === $forma)>{{ ucfirst($forma) }}</option>
            @endforeach
        </select>
        @error('forma_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3 d-none" id="bloque_empresa">
        <label for="empresa_id" class="form-label">Empresa (crédito)</label>
        <select id="empresa_id" name="empresa_id" class="form-select @error('empresa_id') is-invalid @enderror">
            <option value="">Seleccione...</option>
            @foreach ($empresas as $empresa)
                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $reserva?->empresa_id) == $empresa->id)>{{ $empresa->nombre }}</option>
            @endforeach
        </select>
        @error('empresa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">Guardar reserva</button>
    <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>

<script>
(function () {
    const url = @json(route('reservas.disponibilidad'));
    const excluir = @json($reserva?->id);
    const entrada = document.getElementById('fecha_entrada');
    const salida = document.getElementById('fecha_salida');
    const tipo = document.getElementById('tipo_filtro');
    const sel = document.getElementById('habitacion_id');
    const info = document.getElementById('info_disponibilidad');
    const resumen = document.getElementById('resumen');
    const forma = document.getElementById('forma_pago');
    const bloqueEmpresa = document.getElementById('bloque_empresa');
    const empresa = document.getElementById('empresa_id');
    let seleccionada = sel.dataset.selected || '';
    let noches = 0;

    function mostrarResumen() {
        const opt = sel.selectedOptions[0];
        if (opt && opt.dataset.tarifa && noches > 0) {
            const tarifa = Number(opt.dataset.tarifa);
            resumen.textContent = noches + ' noche(s) × Q ' + tarifa.toFixed(2) + ' = Q ' + (noches * tarifa).toFixed(2) + ' (desayuno incluido)';
            resumen.classList.remove('d-none');
        } else {
            resumen.classList.add('d-none');
        }
    }

    function vaciar(mensaje) {
        sel.innerHTML = '';
        sel.add(new Option(mensaje, ''));
        info.textContent = '';
        noches = 0;
        mostrarResumen();
    }

    async function cargar() {
        if (!entrada.value || !salida.value || salida.value <= entrada.value) {
            vaciar('Seleccione fechas válidas...');
            return;
        }

        const params = new URLSearchParams({ entrada: entrada.value, salida: salida.value });
        if (tipo.value) params.set('tipo', tipo.value);
        if (excluir) params.set('excluir', excluir);

        info.textContent = 'Consultando disponibilidad...';

        try {
            const res = await fetch(url + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Error ' + res.status);
            const data = await res.json();

            noches = data.noches;
            sel.innerHTML = '';
            sel.add(new Option(data.habitaciones.length ? 'Seleccione una habitación...' : 'No hay habitaciones disponibles', ''));

            data.habitaciones.forEach(function (h) {
                const texto = h.numero + ' · ' + h.tipo + ' (Q ' + Number(h.tarifa).toFixed(2) + '/noche)';
                const opt = new Option(texto, h.id);
                opt.dataset.tarifa = h.tarifa;
                if (String(h.id) === String(seleccionada)) opt.selected = true;
                sel.add(opt);
            });

            info.textContent = data.habitaciones.length + ' habitación(es) disponible(s) para ' + data.noches + ' noche(s).';
            mostrarResumen();
        } catch (e) {
            info.textContent = 'No se pudo consultar la disponibilidad. Intenta de nuevo.';
        }
    }

    function alternarEmpresa() {
        const esCredito = forma.value === 'credito';
        bloqueEmpresa.classList.toggle('d-none', !esCredito);
        empresa.required = esCredito;
    }

    [entrada, salida, tipo].forEach(function (el) { el.addEventListener('change', cargar); });
    sel.addEventListener('change', function () { seleccionada = sel.value; mostrarResumen(); });
    forma.addEventListener('change', alternarEmpresa);

    alternarEmpresa();
    cargar();
})();
</script>
