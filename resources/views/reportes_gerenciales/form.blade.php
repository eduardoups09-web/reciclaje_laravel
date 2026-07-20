@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo Reporte Gerencial' : 'Editar Reporte Gerencial')

@section('contenido')
@php use App\Models\ReporteGerencial; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar reporte #' . $reporte->id : 'Nuevo reporte gerencial - Roberto' }}
    </h3>
    <a href="{{ route('reportes-gerenciales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

@if ($esEditar)
<form method="get" action="{{ route('reportes-gerenciales.edit', $reporte) }}">
@else
<form method="get" action="{{ route('reportes-gerenciales.create') }}">
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
                    @foreach (ReporteGerencial::MESES as $num => $nombre)
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
<form method="post" action="{{ route('reportes-gerenciales.update', $reporte) }}">
    @csrf
    @method('PUT')
@else
<form method="post" action="{{ route('reportes-gerenciales.store') }}">
    @csrf
@endif
    <input type="hidden" name="mes" value="{{ $mes }}">
    <input type="hidden" name="anio" value="{{ $anio }}">

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">
            Valores — {{ ReporteGerencial::MESES[$mes] }} {{ $anio }}
        </div>
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
                <label class="form-label fw-bold text-primary">Subtotal = Saldo Inicial + Total Recepción</label>
                <input type="text" id="subtotal_display" class="form-control fw-bold bg-primary bg-opacity-10" readonly
                       value="{{ number_format($formulas['subtotal'], 2) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Consumo (MP Nacional + MP Import)</label>
                <input type="number" step="0.01" name="consumo" id="consumo" class="form-control"
                       value="{{ old('consumo', $valores['consumo']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-warning">Saldo Sin Descontar = Subtotal - Consumo</label>
                <input type="text" id="saldo_sin_descontar_display" class="form-control fw-bold bg-warning bg-opacity-10" readonly
                       value="{{ number_format($formulas['saldo_sin_descontar'], 2) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Maquila Enviada</label>
                <input type="number" step="0.01" name="maquila_enviada" id="maquila_enviada" class="form-control"
                       value="{{ old('maquila_enviada', $valores['maquila_enviada']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Maquila Recibida</label>
                <input type="number" step="0.01" name="maquila_recibida" id="maquila_recibida" class="form-control"
                       value="{{ old('maquila_recibida', $valores['maquila_recibida']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold" id="label_saldo_descontado">Saldo Descontado = Saldo Sin Descontar - Maquila Enviada - Maquila Recibida</label>
                <input type="text" id="saldo_descontado_display" class="form-control fw-bold" readonly
                       value="{{ number_format($formulas['saldo_descontado'], 2) }}">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('reportes-gerenciales.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endif
@endsection

@push('scripts')
<script>
    function recalcular() {
        const st  = parseFloat(document.getElementById('saldo_total').value)      || 0;
        const tr  = parseFloat(document.getElementById('total_recepcion').value)  || 0;
        const c   = parseFloat(document.getElementById('consumo').value)          || 0;
        const me  = parseFloat(document.getElementById('maquila_enviada').value)  || 0;
        const mr  = parseFloat(document.getElementById('maquila_recibida').value) || 0;

        const subtotal = st + tr;
        const sinDescontar = subtotal - c;
        const descontado = sinDescontar - me - mr;

        document.getElementById('subtotal_display').value = subtotal.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('saldo_sin_descontar_display').value = sinDescontar.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const el = document.getElementById('saldo_descontado_display');
        const label = document.getElementById('label_saldo_descontado');
        el.value = descontado.toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (descontado < 0) {
            el.className = 'form-control fw-bold bg-danger bg-opacity-10 text-danger';
            label.className = 'form-label fw-bold text-danger';
        } else {
            el.className = 'form-control fw-bold bg-success bg-opacity-10 text-success';
            label.className = 'form-label fw-bold text-success';
        }
    }

    ['saldo_total', 'total_recepcion', 'consumo', 'maquila_enviada', 'maquila_recibida'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', recalcular);
    });
</script>
@endpush
