@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo análisis' : 'Editar análisis')

@section('contenido')
@php use App\Models\AnalisisCalidad; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar análisis #' . $analisis->id : 'Nuevo análisis de calidad' }}
    </h3>
    <a href="{{ route('calidad.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('calidad.update', $analisis) : route('calidad.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos de la muestra</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" class="form-control" required
                       value="{{ old('fecha', $analisis->fecha ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Hora <span class="text-danger">*</span></label>
                <input type="time" name="hora" class="form-control" required
                       value="{{ old('hora', $analisis->hora_corta) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnocalidad" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (AnalisisCalidad::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnocalidad', $analisis->turnocalidad) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="grupocalidad" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (AnalisisCalidad::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('grupocalidad', $analisis->grupocalidad) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Reactor <span class="text-danger">*</span></label>
                <select name="reactor" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (AnalisisCalidad::REACTORES as $r)
                        <option value="{{ $r }}" @selected(old('reactor', $analisis->reactor) === $r)>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtro</label>
                <select name="filtro" class="form-select">
                    <option value="">— Sin filtro —</option>
                    @foreach (AnalisisCalidad::FILTROS as $f)
                        <option value="{{ $f }}" @selected(old('filtro', $analisis->filtro) === $f)>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Mediciones</div>
        <div class="card-body row g-3">
            @foreach (AnalisisCalidad::MEDICIONES as $campo => [$etiqueta, $paso])
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">{{ $etiqueta }}</label>
                    <input type="number" step="{{ $paso }}" min="0" name="{{ $campo }}" class="form-control"
                           value="{{ old($campo, $analisis->$campo) }}" placeholder="0.00">
                </div>
            @endforeach
        </div>
        <div class="card-footer text-muted small">Deja en blanco las mediciones que no apliquen.</div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('calidad.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
