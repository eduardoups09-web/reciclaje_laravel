@php use App\Models\MovimientoDetalle; $esEditar = $modo === 'editar'; @endphp

<form id="formMovDetalle" method="post"
      action="{{ $esEditar ? route('movimiento-detalle.update', $movimientoDetalle) : route('movimiento-detalle.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    @if ($esEditar)
        {{-- MODO EDITAR: 1 registro individual --}}
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
        {{-- MODO CREAR: turnos independientes --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" class="form-control" required
                       value="{{ old('fecha', request('fecha', now()->toDateString())) }}">
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
                           id="switchDiurno"
                           {{ in_array('Diurno', $turnosExistentes ?? []) ? 'disabled' : '' }}
                           {{ old('activar_diurno') ? 'checked' : '' }}>
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
                           id="switchNocturno"
                           {{ in_array('Nocturno', $turnosExistentes ?? []) ? 'disabled' : '' }}
                           {{ old('activar_nocturno') ? 'checked' : '' }}>
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
</form>
