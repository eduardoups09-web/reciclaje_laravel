@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo movimiento de bodega' : 'Editar movimiento')

@section('contenido')
@php use App\Models\Bodega; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        {{ $esEditar ? 'Editar movimiento #' . $bodega->id : 'Nuevo movimiento de bodega' }}
    </h3>
    <a href="{{ route('bodega.index') }}" class="btn btn-outline-secondary">← Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('bodega.update', $bodega) : route('bodega.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del movimiento</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                <input type="date" name="fechainicio" class="form-control" required
                       value="{{ old('fechainicio', $bodega->fechainicio ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha de entrega</label>
                <input type="date" name="fechaemision" class="form-control"
                       value="{{ old('fechaemision', $bodega->fechaemision) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Llegada</label>
                <input type="text" name="llegada" class="form-control" value="{{ old('llegada', $bodega->llegada) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de batería <span class="text-danger">*</span></label>
                <input list="tiposBateria" name="tipobateria" class="form-control" required
                       value="{{ old('tipobateria', $bodega->tipobateria) }}">
                <datalist id="tiposBateria">
                    @foreach (Bodega::TIPOS_BATERIA as $t)<option value="{{ $t }}">@endforeach
                </datalist>
            </div>
            <div class="col-md-3">
                <label class="form-label">Contenedor <span class="text-danger">*</span></label>
                <input type="text" name="contenedor" class="form-control" required value="{{ old('contenedor', $bodega->contenedor) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                <input type="number" step="any" name="cantidad" class="form-control" required
                       value="{{ old('cantidad', $bodega->cantidad) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Unidad</label>
                <select name="unidad" class="form-select">
                    <option value="">Seleccionar…</option>
                    @foreach ($unidades as $u)
                        <option value="{{ $u }}" @selected(old('unidad', $bodega->unidad ?? 'Kilogramos') === $u)>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">N.º despacho <span class="text-danger">*</span></label>
                <select name="despacho" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected(old('despacho', $bodega->despacho) == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Transporte y destinatario <span class="text-muted small fw-normal">(opcional)</span></div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Destinatario</label>
                <input type="text" name="nombreDestinatario" class="form-control" value="{{ old('nombreDestinatario', $bodega->nombreDestinatario) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">RUC destinatario</label>
                <input type="text" name="rucDestinatario" class="form-control" value="{{ old('rucDestinatario', $bodega->rucDestinatario) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Transportista</label>
                <select name="nombreTransportista" id="nombreTransportista" class="form-select">
                    <option value="">Seleccionar…</option>
                    @foreach ($transportistas as $t)
                        <option value="{{ $t }}" @selected(old('nombreTransportista', $bodega->nombreTransportista) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">RUC transportista</label>
                <input type="text" name="transportistaRuc" class="form-control" value="{{ old('transportistaRuc', $bodega->transportistaRuc) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Placa</label>
                <input type="text" name="placaTransportista" class="form-control" value="{{ old('placaTransportista', $bodega->placaTransportista) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Observación</label>
                <input type="text" name="observacion" class="form-control" value="{{ old('observacion', $bodega->observacion) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Motivo</label>
                <select name="motivo" class="form-select">
                    <option value="">Seleccionar…</option>
                    @foreach ($motivos as $m)
                        <option value="{{ $m }}" @selected(old('motivo', $bodega->motivo) === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Partida</label>
                <input type="text" name="partida" class="form-control" value="{{ old('partida', $bodega->partida) }}">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg">Guardar</button>
    <a href="{{ route('bodega.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection

@push('scripts')
<script>
(function() {
    const form = document.querySelector('form[method="post"]');
    if (!form) return;

    const fechaInput = form.querySelector('[name="fechainicio"]');
    const despachoInput = form.querySelector('[name="despacho"]');

    let hiddenInput = form.querySelector('input[name="consecutivo"][type="hidden"]');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'consecutivo';
        form.appendChild(hiddenInput);
    }

    function obtenerConsecutivo() {
        const fecha = fechaInput.value;
        const despacho = despachoInput.value;
        if (!fecha || !despacho) return;

        fetch(`/bodega/consecutivo?fecha=${fecha}&despacho=${despacho}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            hiddenInput.value = data.consecutivo;
        });
    }

    fechaInput.addEventListener('change', obtenerConsecutivo);
    despachoInput.addEventListener('change', obtenerConsecutivo);
})();
</script>

@push('scripts')
<script>
(function() {
    // Auto-cargar RUC y Placa al seleccionar transportista
    function initTransportistaAutoload() {
        const select = document.getElementById('nombreTransportista');
        if (!select) return;
        select.addEventListener('change', function() {
            const nombre = this.value;
            if (!nombre) return;
            fetch(`/transportistas/obtener?nombre=${encodeURIComponent(nombre)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const ruc = document.querySelector('[name="transportistaRuc"]');
                const placa = document.querySelector('[name="placaTransportista"]');
                if (ruc) ruc.value = data.ruc || '';
                if (placa) placa.value = data.placa || '';
            });
        });
    }

    initTransportistaAutoload();
})();
</script>
@endpush
