@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo saldo' : 'Editar saldo')

@section('contenido')
@php use App\Models\Saldosinsert; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar saldo #' . $saldo->id : 'Nuevo saldo de inventario' }}
    </h3>
    <a href="{{ route('saldos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('saldos.update', $saldo) : route('saldos.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Periodo</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fechasaldoinsert" class="form-control" required
                       value="{{ old('fechasaldoinsert', $saldo->fechasaldoinsert ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnosaldoinsert" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Saldosinsert::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnosaldoinsert', $saldo->turnosaldoinsert) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Cantidades <span class="text-muted small fw-normal">(pueden ser negativas)</span></div>
        <div class="card-body row g-3">
            @foreach (Saldosinsert::CANTIDADES as $campo => $etiqueta)
                <div class="col-md-4">
                    <label class="form-label">{{ $etiqueta }}</label>
                    <input type="number" step="1" name="{{ $campo }}" class="form-control"
                           value="{{ old($campo, $saldo->$campo) }}" placeholder="0">
                </div>
            @endforeach
        </div>
    </div>

    @if ($esEditar && $saldo->id)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">
            <i class="bi bi-calculator text-info"></i> Valores Calculados (solo lectura)
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Recepciones --}}
                <div class="col-md-4">
                    <label class="form-label small text-muted">Total Recepción</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->total_recepcion, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Rec. Nac. Automotriz</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->recepcion_nacional_automotriz, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Rec. Nac. UPS</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->recepcion_nacional_ups, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Rec. Imp. Automotriz</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->recepcion_importada_automotriz, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Rec. Imp. UPS</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->recepcion_importada_ups, 2) }}">
                </div>

                {{-- Consumo y Maquila --}}
                <div class="col-12"><hr class="my-2"></div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Consumo</label>
                    <input type="text" class="form-control fw-bold" readonly
                           value="{{ number_format($saldo->consumo, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Maquila Enviada</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->maquila_enviada, 2) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Maquila Recibida</label>
                    <input type="text" class="form-control" readonly
                           value="{{ number_format($saldo->maquila_recibida, 2) }}">
                </div>
            </div>
        </div>
    </div>
    @endif

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('saldos.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
