@php use App\Models\MpNacional; $esEditar = $modo === 'editar'; @endphp

<form id="formMpNacional" method="post"
      action="{{ $esEditar ? route('mpnacional.update', $mpnacional) : route('mpnacional.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="hidden" name="fechanacional"
                   value="{{ old('fechanacional', $mpnacional->fechanacional ?? request('fecha', now()->toDateString())) }}">
            <input type="date" class="form-control" disabled readonly
                   value="{{ old('fechanacional', $mpnacional->fechanacional ?? request('fecha', now()->toDateString())) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Grupo <span class="text-danger">*</span></label>
            <input type="hidden" name="gruponacional"
                   value="{{ old('gruponacional', $mpnacional->gruponacional ?? request('grupo', '')) }}">
            <select class="form-select" disabled>
                @foreach (MpNacional::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(old('gruponacional', $mpnacional->gruponacional ?? request('grupo')) === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Turno <span class="text-danger">*</span></label>
            <input type="hidden" name="turnonacional"
                   value="{{ old('turnonacional', $mpnacional->turnonacional ?? request('turno', '')) }}">
            <select class="form-select" disabled>
                @foreach (MpNacional::TURNOS as $t)
                    <option value="{{ $t }}" @selected(old('turnonacional', $mpnacional->turnonacional ?? request('turno')) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipo de batería <span class="text-danger">*</span></label>
            <select name="bateriatipo" class="form-select" required>
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
</form>
