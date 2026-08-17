@php use App\Models\AnalisisCalidad; $esEditar = $modo === 'editar'; @endphp

<form id="formCalidad" method="post"
      action="{{ $esEditar ? route('calidad.update', $analisis) : route('calidad.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="hidden" name="fecha"
                   value="{{ old('fecha', $analisis->fecha ?? request('fecha', now()->toDateString())) }}">
            <input type="date" class="form-control" disabled readonly
                   value="{{ old('fecha', $analisis->fecha ?? request('fecha', now()->toDateString())) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hora <span class="text-danger">*</span></label>
            <input type="time" name="hora" class="form-control" required
                   value="{{ old('hora', $analisis->hora_corta ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Turno <span class="text-danger">*</span></label>
            <input type="hidden" name="turnocalidad"
                   value="{{ old('turnocalidad', $analisis->turnocalidad ?? request('turno', '')) }}">
            <select class="form-select" disabled>
                @foreach (AnalisisCalidad::TURNOS as $t)
                    <option value="{{ $t }}" @selected(old('turnocalidad', $analisis->turnocalidad ?? request('turno')) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Grupo <span class="text-danger">*</span></label>
            <input type="hidden" name="grupocalidad"
                   value="{{ old('grupocalidad', $analisis->grupocalidad ?? request('grupo', '')) }}">
            <select class="form-select" disabled>
                @foreach (AnalisisCalidad::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(old('grupocalidad', $analisis->grupocalidad ?? request('grupo')) === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Filtro <span class="text-danger">*</span></label>
            <select name="filtro" class="form-select" required>
                @foreach (AnalisisCalidad::FILTROS as $f)
                    <option value="{{ $f }}" @selected(old('filtro', $analisis->filtro) === $f)>{{ $f }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-9">
            <label class="form-label">Reactor <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                @foreach (['reactor1' => 'R1', 'reactor2' => 'R2', 'reactor3' => 'R3', 'reactor4' => 'R4'] as $campo => $etiqueta)
                    <div class="text-center" style="width: 60px;">
                        <input type="hidden" name="{{ $campo }}" value="0">
                        <input type="checkbox" name="{{ $campo }}" value="1" class="form-check-input"
                            id="modal_{{ $campo }}"
                            {{ old($campo, $analisis->$campo) ? 'checked' : '' }}>
                        <label class="form-check-label d-block mt-1" for="modal_{{ $campo }}">{{ $etiqueta }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <hr class="my-3">

    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Temperatura (°C)</label>
            <input type="number" step="0.01" min="0" name="temperatura" class="form-control"
                   value="{{ old('temperatura', $analisis->temperatura) }}" placeholder="0.00">
        </div>
        <div class="col-md-3">
            <label class="form-label">pH</label>
            <input type="number" step="0.01" min="0" name="ph" class="form-control"
                   value="{{ old('ph', $analisis->ph) }}" placeholder="0.00">
        </div>
        <div class="col-md-3">
            <label class="form-label">Azufre (%)</label>
            <input type="number" step="0.01" min="0" name="azufre" class="form-control"
                   value="{{ old('azufre', $analisis->azufre) }}" placeholder="0.00">
        </div>
    </div>
    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <label class="form-label">PI (Peso Inicial)</label>
            <input type="number" step="0.01" min="0" name="pi" class="form-control" id="modal_pi" placeholder="0.00"
                   value="{{ old('pi', $analisis->pi) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">PF (Peso Final)</label>
            <input type="number" step="0.01" min="0" name="pf" class="form-control" id="modal_pf" placeholder="0.00"
                   value="{{ old('pf', $analisis->pf) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Humedad (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="humedad" class="form-control" id="modal_humedad"
                   value="{{ old('humedad', !is_null($analisis->humedad) ? number_format($analisis->humedad, 2, '.', '') : '') }}"
                   placeholder="Auto" readonly>
        </div>
    </div>
</form>

<script>
(function() {
    const pi = document.getElementById('modal_pi');
    const pf = document.getElementById('modal_pf');
    const hum = document.getElementById('modal_humedad');
    if (!pi || !pf || !hum) return;
    function calc() {
        const p = parseFloat(pi.value) || 0;
        const f = parseFloat(pf.value) || 0;
        hum.value = p > 0 ? (((p - f) / p * 100) + 0.6).toFixed(2) : '';
    }
    pi.addEventListener('input', calc);
    pf.addEventListener('input', calc);
    if (!hum.value) calc();
})();
</script>
