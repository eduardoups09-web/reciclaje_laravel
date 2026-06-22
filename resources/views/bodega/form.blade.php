@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo movimiento de bodega' : 'Editar movimiento')

@section('contenido')
@php use App\Models\Bodega; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar movimiento #' . $bodega->id : 'Nuevo movimiento de bodega' }}
    </h3>
    <a href="{{ route('bodega.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('bodega.update', $bodega) : route('bodega.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del movimiento</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fechainicio" class="form-control" required
                       value="{{ old('fechainicio', $bodega->fechainicio ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="grupo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Bodega::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('grupo', $bodega->grupo) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turno" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Bodega::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turno', $bodega->turno) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de batería</label>
                <input list="tiposBateria" name="tipobateria" class="form-control"
                       value="{{ old('tipobateria', $bodega->tipobateria) }}">
                <datalist id="tiposBateria">
                    @foreach (Bodega::TIPOS_BATERIA as $t)<option value="{{ $t }}">@endforeach
                </datalist>
            </div>
            <div class="col-md-3">
                <label class="form-label">Contenedor</label>
                <input type="text" name="contenedor" class="form-control" value="{{ old('contenedor', $bodega->contenedor) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                <input type="number" step="any" name="cantidad" class="form-control" required
                       value="{{ old('cantidad', $bodega->cantidad) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Unidad</label>
                <input list="unidades" name="unidad" class="form-control" value="{{ old('unidad', $bodega->unidad) }}">
                <datalist id="unidades">
                    @foreach (Bodega::UNIDADES as $u)<option value="{{ $u }}">@endforeach
                </datalist>
            </div>
            <div class="col-md-3">
                <label class="form-label">Consecutivo</label>
                <input type="number" name="consecutivo" class="form-control" value="{{ old('consecutivo', $bodega->consecutivo) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">N.º despacho</label>
                <input type="text" name="despacho" class="form-control" value="{{ old('despacho', $bodega->despacho) }}">
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
                <input type="text" name="nombreTransportista" class="form-control" value="{{ old('nombreTransportista', $bodega->nombreTransportista) }}">
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
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('bodega.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
