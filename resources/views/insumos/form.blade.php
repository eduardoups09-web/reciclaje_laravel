@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo insumo' : 'Editar insumo')

@section('contenido')
@php use App\Models\Insumo; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar insumo #' . $insumo->id : 'Nuevo registro de insumos' }}
    </h3>
    <a href="{{ route('operaciones.index', ['tab' => 'insumos']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('insumos.update', $insumo) : route('insumos.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del turno</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" class="form-control" required
                       value="{{ old('fecha', $insumo->fecha ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="grupoinsumo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Insumo::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('grupoinsumo', $insumo->grupoinsumo) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnoinsumo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Insumo::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnoinsumo', $insumo->turnoinsumo) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Cantidades</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Carbonato de sodio</label>
                <input type="number" min="0" step="1" name="carbonatoSodio" class="form-control"
                       value="{{ old('carbonatoSodio', $insumo->carbonatoSodio) }}" placeholder="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cal</label>
                <input type="number" min="0" step="1" name="cal" class="form-control"
                       value="{{ old('cal', $insumo->cal) }}" placeholder="0">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'insumos']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
