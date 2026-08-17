@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nueva producción' : 'Editar producción')

@section('contenido')
@php use App\Models\Salida; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar registro #' . $salida->id : 'Nuevo registro de producción' }}
    </h3>
    <a href="{{ route('operaciones.index', ['tab' => 'produccion']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('produccion.update', $salida) : route('produccion.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del turno</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fechasalida" class="form-control" required
                       value="{{ old('fechasalida', $salida->fechasalida ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="gruposalida" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Salida::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('gruposalida', $salida->gruposalida) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnosalida" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Salida::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnosalida', $salida->turnosalida) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Productos recuperados (kg / unidades)</div>
        <div class="card-body row g-3">
            @foreach (Salida::CAMPOS_NUMERICOS as $campo => $etiqueta)
                @if (in_array($campo, Salida::CAMPOS_CON_FACTOR))
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">{{ $etiqueta }}</label>
                        <div class="input-group">
                            <input type="number" min="0" step="1" name="{{ $campo }}" class="form-control"
                                   value="{{ old($campo, $salida->$campo) }}" placeholder="0">
                            <select name="factor_{{ $campo }}" class="form-select" style="max-width: 90px;">
                                @foreach (Salida::FACTORES as $f)
                                    <option value="{{ $f }}" @selected(old("factor_{$campo}", 0.97) == $f)>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label">{{ $etiqueta }}</label>
                        <input type="number" min="0" step="1" name="{{ $campo }}" class="form-control"
                               value="{{ old($campo, $salida->$campo) }}" placeholder="0">
                    </div>
                @endif
            @endforeach
        </div>
        <div class="card-footer text-muted small">Deja en blanco los productos que no apliquen a este turno.</div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'produccion']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
