@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nueva MP Importación' : 'Editar MP Importación')

@section('contenido')
@php use App\Models\MpImport; $esEditar = $modo === 'editar'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-{{ $esEditar ? 'pencil-square' : 'plus-circle' }} text-success"></i>
        {{ $esEditar ? 'Editar registro #' . $mpimport->id : 'Nuevo registro de MP Importación' }}
    </h3>
    <a href="{{ route('operaciones.index', ['tab' => 'mpimport']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<form method="post" action="{{ $esEditar ? route('mpimport.update', $mpimport) : route('mpimport.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Datos del turno</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fechaimport" class="form-control" required
                       value="{{ old('fechaimport', $mpimport->fechaimport ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="grupoimport" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MpImport::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('grupoimport', $mpimport->grupoimport) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnoimport" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (MpImport::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnoimport', $mpimport->turnoimport) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Materia prima importada</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipo de batería</label>
                <select name="bateriatipoimport" class="form-select">
                    <option value="">— Seleccionar —</option>
                    @foreach (MpImport::TIPOS_BATERIA as $tb)
                        <option value="{{ $tb }}" @selected(old('bateriatipoimport', $mpimport->bateriatipoimport) === $tb)>{{ $tb }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Peso batería</label>
                <input type="number" min="0" step="1" name="pesobateriaimport" class="form-control"
                       value="{{ old('pesobateriaimport', $mpimport->pesobateriaimport) }}" placeholder="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Metálico</label>
                <input type="number" min="0" step="1" name="metalicoimport" class="form-control"
                       value="{{ old('metalicoimport', $mpimport->metalicoimport) }}" placeholder="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Pasta</label>
                <input type="number" min="0" step="1" name="pastaimport" class="form-control"
                       value="{{ old('pastaimport', $mpimport->pastaimport) }}" placeholder="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Placas</label>
                <input type="number" min="0" step="1" name="placasimport" class="form-control"
                       value="{{ old('placasimport', $mpimport->placasimport) }}" placeholder="0">
            </div>
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'mpimport']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
