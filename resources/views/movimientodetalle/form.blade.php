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
        <div class="card-body">
            @if ($esEditar)
                {{-- MODO EDITAR --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control" required
                               value="{{ old('fecha', $movimientoDetalle->fecha ?? now()->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Turno</label>
                        <div class="form-control bg-light">{{ $movimientoDetalle->turno }}</div>
                        <input type="hidden" name="turno" value="{{ $movimientoDetalle->turno }}">
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
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="status_id" class="form-select" required>
                            @foreach (MovimientoDetalle::ESTADOS as $id => $label)
                                <option value="{{ $id }}" @selected(old('status_id', $movimientoDetalle->status_id ?? 1) == $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                {{-- MODO CREAR --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control" required
                               value="{{ old('fecha', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="status_id" class="form-select" required>
                            @foreach (MovimientoDetalle::ESTADOS as $id => $label)
                                <option value="{{ $id }}" @selected(old('status_id', 1) == $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activar_diurno" value="1"
                                   id="switchDiurno" {{ old('activar_diurno') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-success" for="switchDiurno">
                                <i class="bi bi-sun-fill"></i> Diurno (7am – 7pm)
                            </label>
                        </div>
                        <div class="ms-4 mt-1">
                            <select name="grupo_diurno" class="form-select" id="grupoDiurno"
                                    style="max-width:200px" {{ old('activar_diurno') ? '' : 'disabled' }}>
                                <option value="">Seleccionar grupo…</option>
                                @foreach (MovimientoDetalle::GRUPOS as $g)
                                    <option value="{{ $g }}" @selected(old('grupo_diurno') === $g)>Grupo {{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activar_nocturno" value="1"
                                   id="switchNocturno" {{ old('activar_nocturno') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-primary" for="switchNocturno">
                                <i class="bi bi-moon-fill"></i> Nocturno (7pm – 7am)
                            </label>
                        </div>
                        <div class="ms-4 mt-1">
                            <select name="grupo_nocturno" class="form-select" id="grupoNocturno"
                                    style="max-width:200px" {{ old('activar_nocturno') ? '' : 'disabled' }}>
                                <option value="">Seleccionar grupo…</option>
                                @foreach (MovimientoDetalle::GRUPOS as $g)
                                    <option value="{{ $g }}" @selected(old('grupo_nocturno') === $g)>Grupo {{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <button class="btn btn-success btn-lg"><i class="bi bi-save"></i> Guardar</button>
    <a href="{{ route('operaciones.index', ['tab' => 'movdetalle']) }}" class="btn btn-light btn-lg">Cancelar</a>
</form>

@if (!$esEditar)
@push('scripts')
<script>
document.getElementById('switchDiurno').addEventListener('change', function() {
    document.getElementById('grupoDiurno').disabled = !this.checked;
    if (!this.checked) document.getElementById('grupoDiurno').value = '';
});
document.getElementById('switchNocturno').addEventListener('change', function() {
    document.getElementById('grupoNocturno').disabled = !this.checked;
    if (!this.checked) document.getElementById('grupoNocturno').value = '';
});
</script>
@endpush
@endif
@endsection
