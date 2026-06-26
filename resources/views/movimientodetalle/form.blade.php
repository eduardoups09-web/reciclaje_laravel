@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo movimiento detalle' : 'Editar movimiento detalle')

@section('contenido')
@php use App\Models\MovimientoDetalle; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar movimiento #' . $movimientoDetalle->id : 'Nuevo movimiento detalle' }}
    </h3>
    <a href="{{ route('operaciones.index', ['tab' => 'movdetalle']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('movimiento-detalle.update', $movimientoDetalle) : route('movimiento-detalle.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del movimiento</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" class="form-control" required
                       value="{{ old('fecha', $movimientoDetalle->fecha ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="grupo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MovimientoDetalle::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('grupo', $movimientoDetalle->grupo) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turno" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MovimientoDetalle::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turno', $movimientoDetalle->turno) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'movdetalle']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
