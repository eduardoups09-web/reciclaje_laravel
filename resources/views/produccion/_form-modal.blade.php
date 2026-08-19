@php use App\Models\Salida; $esEditar = $modo === 'editar'; @endphp

<form id="formSalida" method="post"
      action="{{ $esEditar ? route('produccion.update', $salida) : route('produccion.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="hidden" name="fechasalida"
                   value="{{ old('fechasalida', $salida->fechasalida ?? request('fecha', now()->toDateString())) }}">
            <input type="date" class="form-control" disabled readonly
                   value="{{ old('fechasalida', $salida->fechasalida ?? request('fecha', now()->toDateString())) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Grupo <span class="text-danger">*</span></label>
            <input type="hidden" name="gruposalida"
                   value="{{ old('gruposalida', $salida->gruposalida ?? request('grupo', '')) }}">
            <select class="form-select" disabled>
                @foreach (Salida::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(old('gruposalida', $salida->gruposalida ?? request('grupo')) === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Turno <span class="text-danger">*</span></label>
            <input type="hidden" name="turnosalida"
                   value="{{ old('turnosalida', $salida->turnosalida ?? request('turno', '')) }}">
            <select class="form-select" disabled>
                @foreach (Salida::TURNOS as $t)
                    <option value="{{ $t }}" @selected(old('turnosalida', $salida->turnosalida ?? request('turno')) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr class="my-3">

    <div class="row g-3">
        @foreach (Salida::CAMPOS_NUMERICOS as $campo => $etiqueta)
            @if (in_array($campo, Salida::CAMPOS_CON_FACTOR))
                <div class="col-md-4 col-sm-6">
                    <label class="form-label">{{ $etiqueta }}</label>
                    <div class="input-group">
                        <input type="number" min="0" step="1" name="{{ $campo }}" class="form-control campo-cantidad"
                               data-campo="{{ $campo }}" data-columna="{{ Salida::CAMPOS_FACTOR_MAP[$campo] }}"
                               value="{{ old($campo, $salida->$campo) }}" placeholder="0">
                        <select name="factor_{{ $campo }}" class="form-select campo-factor" style="max-width: 90px;"
                                data-campo="{{ $campo }}">
                            @foreach (Salida::FACTORES as $f)
                                <option value="{{ $f }}" @selected(old("factor_{$campo}", $salida->{Salida::CAMPOS_FACTOR_MAP[$campo]} ?? 0.97) == $f)>{{ $f }}</option>
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
</form>
