@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo Reporte Gerencial Pablo' : 'Editar Reporte Gerencial Pablo')

@section('contenido')
@php use App\Models\ReporteGerencialPablo; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar reporte #' . $reporte->id : 'Nuevo reporte gerencial - Pablo' }}
    </h3>
    <a href="{{ route('pablo.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

@if ($esEditar)
<form method="get" action="{{ route('pablo.edit', $reporte) }}">
@else
<form method="get" action="{{ route('pablo.create') }}">
@endif
    @csrf
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Seleccionar período</div>
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Año <span class="text-danger">*</span></label>
                <select name="anio" class="form-select" required>
                    @for ($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}" @selected($anio == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mes <span class="text-danger">*</span></label>
                <select name="mes" class="form-select" required>
                    @foreach (ReporteGerencialPablo::MESES as $num => $nombre)
                        <option value="{{ $num }}" @selected($mes == $num)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-calculator"></i> Calcular</button>
            </div>
        </div>
    </div>
</form>

@if ($valores)
@if ($esEditar)
<form method="post" action="{{ route('pablo.update', $reporte) }}">
    @csrf
    @method('PUT')
@else
<form method="post" action="{{ route('pablo.store') }}">
    @csrf
@endif
    <input type="hidden" name="mes" value="{{ $mes }}">
    <input type="hidden" name="anio" value="{{ $anio }}">

    {{-- Sección 1: Saldos --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Saldos y Totales</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Saldo Inicial</label>
                <input type="number" step="0.01" name="saldo_total" id="saldo_total" class="form-control"
                       value="{{ old('saldo_total', $valores['saldo_total']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Recepción</label>
                <input type="number" step="0.01" name="total_recepcion" id="total_recepcion" class="form-control"
                       value="{{ old('total_recepcion', $valores['total_recepcion']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Consumo</label>
                <input type="text" id="consumo_display" class="form-control fw-bold bg-light" readonly
                       value="{{ number_format($valores['consumo'], 2) }}">
            </div>
        </div>
    </div>

    {{-- Sección 2: Recepción por tipo --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white fw-semibold">
            <i class="bi bi-box-arrow-in-down"></i> Recepción por Tipo de Batería (Ingresos)
        </div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label text-success fw-semibold">Nacional Automotriz</label>
                <small class="d-block text-muted mb-1">Humedas Nac + Humedas Maquila</small>
                <input type="number" step="0.01" name="recepcion_nacional_automotriz" id="recepcion_nacional_automotriz" class="form-control"
                       value="{{ old('recepcion_nacional_automotriz', $valores['recepcion_nacional_automotriz']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-success fw-semibold">Nacional UPS</label>
                <small class="d-block text-muted mb-1">Estacionarias Nac</small>
                <input type="number" step="0.01" name="recepcion_nacional_ups" id="recepcion_nacional_ups" class="form-control"
                       value="{{ old('recepcion_nacional_ups', $valores['recepcion_nacional_ups']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-info fw-semibold">Importado Automotriz</label>
                <small class="d-block text-muted mb-1">Golf + Humedas Ext</small>
                <input type="number" step="0.01" name="recepcion_importada_automotriz" id="recepcion_importada_automotriz" class="form-control"
                       value="{{ old('recepcion_importada_automotriz', $valores['recepcion_importada_automotriz']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-info fw-semibold">Importado UPS</label>
                <small class="d-block text-muted mb-1">Estacionarias Ext</small>
                <input type="number" step="0.01" name="recepcion_importada_ups" id="recepcion_importada_ups" class="form-control"
                       value="{{ old('recepcion_importada_ups', $valores['recepcion_importada_ups']) }}">
            </div>
        </div>
    </div>

    {{-- Sección 3: Consumo --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-fire"></i> Consumo de Baterías
        </div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label text-primary">Nac. Automotriz</label>
                <input type="number" step="0.01" name="bateria_nacional_automotriz" id="bateria_nacional_automotriz" class="form-control"
                       value="{{ old('bateria_nacional_automotriz', $valores['bateria_nacional_automotriz']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-primary">Nac. UPS</label>
                <input type="number" step="0.01" name="bateria_nacional_ups" id="bateria_nacional_ups" class="form-control"
                       value="{{ old('bateria_nacional_ups', $valores['bateria_nacional_ups']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-warning">Imp. Automotriz</label>
                <input type="number" step="0.01" name="bateria_importada_automotriz" id="bateria_importada_automotriz" class="form-control"
                       value="{{ old('bateria_importada_automotriz', $valores['bateria_importada_automotriz']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-warning">Imp. UPS</label>
                <input type="number" step="0.01" name="bateria_importada_ups" id="bateria_importada_ups" class="form-control"
                       value="{{ old('bateria_importada_ups', $valores['bateria_importada_ups']) }}">
            </div>
        </div>
    </div>

    {{-- Sección 4: Maquila y Saldos --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Maquila y Saldos por Tipo</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">Maquila Enviada</label>
                <input type="number" step="0.01" name="maquila_enviada" id="maquila_enviada" class="form-control"
                       value="{{ old('maquila_enviada', $valores['maquila_enviada']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Maquila Recibida</label>
                <input type="number" step="0.01" name="maquila_recibida" id="maquila_recibida" class="form-control"
                       value="{{ old('maquila_recibida', $valores['maquila_recibida']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Total Maquila</label>
                <input type="text" id="total_maquila_display" class="form-control fw-bold bg-light" readonly
                       value="{{ number_format($valores['total_maquila'] ?? 0, 2) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" id="label_saldo_cierre">Saldo Cierre</label>
                <input type="text" id="saldo_cierre_display" class="form-control fw-bold" readonly
                       value="{{ number_format($formulas['saldo_cierre'], 2) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" id="label_sc_auto">Saldo Cierre Automotriz</label>
                <input type="text" id="sc_auto_display" class="form-control fw-bold" readonly
                       value="{{ number_format($valores['saldo_cierre_automotriz'] ?? 0, 2) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold" id="label_sc_ups">Saldo Cierre UPS</label>
                <input type="text" id="sc_ups_display" class="form-control fw-bold" readonly
                       value="{{ number_format($valores['saldo_cierre_ups'] ?? 0, 2) }}">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('pablo.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endif
@endsection

@push('scripts')
<script>
    function recalcular() {
        const st  = parseFloat(document.getElementById('saldo_total').value) || 0;
        const tr  = parseFloat(document.getElementById('total_recepcion').value) || 0;
        const bna = parseFloat(document.getElementById('bateria_nacional_automotriz').value) || 0;
        const bnu = parseFloat(document.getElementById('bateria_nacional_ups').value) || 0;
        const bia = parseFloat(document.getElementById('bateria_importada_automotriz').value) || 0;
        const biu = parseFloat(document.getElementById('bateria_importada_ups').value) || 0;
        const me  = parseFloat(document.getElementById('maquila_enviada').value) || 0;
        const mr  = parseFloat(document.getElementById('maquila_recibida').value) || 0;

        const consumo = bna + bnu + bia + biu;
        const totalMaquila = me + mr;
        const saldoCierre = st + tr - consumo - me - mr;

        document.getElementById('consumo_display').value = consumo.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('total_maquila_display').value = totalMaquila.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const el = document.getElementById('saldo_cierre_display');
        const label = document.getElementById('label_saldo_cierre');
        el.value = saldoCierre.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        el.className = saldoCierre < 0 ? 'form-control fw-bold bg-danger bg-opacity-10 text-danger' : 'form-control fw-bold bg-success bg-opacity-10 text-success';
        label.className = saldoCierre < 0 ? 'form-label fw-bold text-danger' : 'form-label fw-bold text-success';
    }

    ['saldo_total', 'total_recepcion',
     'bateria_nacional_automotriz', 'bateria_nacional_ups',
     'bateria_importada_automotriz', 'bateria_importada_ups',
     'maquila_enviada', 'maquila_recibida'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', recalcular);
    });
</script>
@endpush
