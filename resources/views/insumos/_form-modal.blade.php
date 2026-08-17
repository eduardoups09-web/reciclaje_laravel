@php use App\Models\Insumo; $esEditar = $modo === 'editar'; @endphp

<form id="formInsumo" method="post"
      action="{{ $esEditar ? route('insumos.update', $insumo) : route('insumos.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="hidden" name="fecha"
                   value="{{ old('fecha', $insumo->fecha ?? request('fecha', now()->toDateString())) }}">
            <input type="date" class="form-control" disabled readonly
                   value="{{ old('fecha', $insumo->fecha ?? request('fecha', now()->toDateString())) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Grupo <span class="text-danger">*</span></label>
            <input type="hidden" name="grupoinsumo"
                   value="{{ old('grupoinsumo', $insumo->grupoinsumo ?? request('grupo', '')) }}">
            <select class="form-select" disabled>
                @foreach (Insumo::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(old('grupoinsumo', $insumo->grupoinsumo ?? request('grupo')) === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Turno <span class="text-danger">*</span></label>
            <input type="hidden" name="turnoinsumo"
                   value="{{ old('turnoinsumo', $insumo->turnoinsumo ?? request('turno', '')) }}">
            <select class="form-select" disabled>
                @foreach (Insumo::TURNOS as $t)
                    <option value="{{ $t }}" @selected(old('turnoinsumo', $insumo->turnoinsumo ?? request('turno')) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Carbonato de sodio</label>
            <input type="number" min="0" step="1" name="carbonatoSodio" class="form-control"
                   value="{{ old('carbonatoSodio', $insumo->carbonatoSodio) }}" placeholder="0">
        </div>
    </div>
</form>
