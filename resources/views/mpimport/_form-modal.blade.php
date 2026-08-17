@php use App\Models\MpImport; $esEditar = $modo === 'editar'; @endphp

<form id="formMpImport" method="post"
      action="{{ $esEditar ? route('mpimport.update', $mpimport) : route('mpimport.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="hidden" name="fechaimport"
                   value="{{ old('fechaimport', $mpimport->fechaimport ?? request('fecha', now()->toDateString())) }}">
            <input type="date" class="form-control" disabled readonly
                   value="{{ old('fechaimport', $mpimport->fechaimport ?? request('fecha', now()->toDateString())) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Grupo <span class="text-danger">*</span></label>
            <input type="hidden" name="grupoimport"
                   value="{{ old('grupoimport', $mpimport->grupoimport ?? request('grupo', '')) }}">
            <select class="form-select" disabled>
                @foreach (MpImport::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(old('grupoimport', $mpimport->grupoimport ?? request('grupo')) === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Turno <span class="text-danger">*</span></label>
            <input type="hidden" name="turnoimport"
                   value="{{ old('turnoimport', $mpimport->turnoimport ?? request('turno', '')) }}">
            <select class="form-select" disabled>
                @foreach (MpImport::TURNOS as $t)
                    <option value="{{ $t }}" @selected(old('turnoimport', $mpimport->turnoimport ?? request('turno')) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipo de batería <span class="text-danger">*</span></label>
            <select name="bateriatipoimport" class="form-select" required>
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
</form>
