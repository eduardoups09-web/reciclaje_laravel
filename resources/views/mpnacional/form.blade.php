@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nueva MP Nacional' : 'Editar MP Nacional')

@section('contenido')
@php use App\Models\MpNacional; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar registro #' . $mpnacional->id : 'Nuevo registro de MP Nacional' }}
    </h3>
    <a href="{{ route('operaciones.index', ['tab' => 'mpnacional']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('mpnacional.update', $mpnacional) : route('mpnacional.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del turno</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fechanacional" class="form-control" required
                       value="{{ old('fechanacional', $mpnacional->fechanacional ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="gruponacional" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MpNacional::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('gruponacional', $mpnacional->gruponacional) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnonacional" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MpNacional::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnonacional', $mpnacional->turnonacional) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Materia prima nacional</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipo de batería</label>
                <select name="bateriatipo" class="form-select">
                    <option value="">— Seleccionar —</option>
                    @foreach (MpNacional::TIPOS_BATERIA as $tb)
                        <option value="{{ $tb }}" @selected(old('bateriatipo', $mpnacional->bateriatipo) === $tb)>{{ $tb }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Peso batería</label>
                <input type="number" min="0" step="1" name="pesobateria" class="form-control"
                       value="{{ old('pesobateria', $mpnacional->pesobateria) }}" placeholder="0">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'mpnacional']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
