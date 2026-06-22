@extends('layouts.app')
@section('titulo', $modo === 'crear' ? 'Nuevo saldo' : 'Editar saldo')

@section('contenido')
@php use App\Models\Saldo; $esEditar = $modo === 'editar'; @endphp

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
                <input type="date" name="fechasaldo" class="form-control" required
                       value="{{ old('fechasaldo', $saldo->fechasaldo ?? now()->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grupo <span class="text-danger">*</span></label>
                <select name="gruposaldo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Saldo::GRUPOS as $g)
                        <option value="{{ $g }}" @selected(old('gruposaldo', $saldo->gruposaldo) === $g)>Grupo {{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Turno <span class="text-danger">*</span></label>
                <select name="turnosaldo" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    @foreach (Saldo::TURNOS as $t)
                        <option value="{{ $t }}" @selected(old('turnosaldo', $saldo->turnosaldo) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold">Cantidades <span class="text-muted small fw-normal">(pueden ser negativas)</span></div>
        <div class="card-body row g-3">
            @foreach (Saldo::CANTIDADES as $campo => $etiqueta)
                <div class="col-md-4">
                    <label class="form-label">{{ $etiqueta }}</label>
                    <input type="number" step="1" name="{{ $campo }}" class="form-control"
                           value="{{ old($campo, $saldo->$campo) }}" placeholder="0">
                </div>
            @endforeach
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('saldos.index') }}" class="btn btn-light btn-lg">Cancelar</a>
</form>
@endsection
