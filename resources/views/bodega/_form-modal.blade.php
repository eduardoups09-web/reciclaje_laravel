@php use App\Models\Bodega; $esEditar = $modo === 'editar'; @endphp

<form id="formBodega" method="post"
      action="{{ $esEditar ? route('bodega.update', $bodega) : route('bodega.store') }}">
    @csrf
    @if ($esEditar) @method('PUT') @endif

    {{-- Sección 1: Despacho --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2 fw-semibold">Despacho</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" name="fechainicio" class="form-control" required
                           value="{{ old('fechainicio', $bodega->fechainicio ?? now()->toDateString()) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha de entrega</label>
                    <input type="date" name="fechaemision" class="form-control"
                           value="{{ old('fechaemision', $bodega->fechaemision) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">N.º despacho <span class="text-danger">*</span></label>
                    <select name="despacho" class="form-select" required>
                        <option value="">Seleccionar…</option>
                        @for ($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected(old('despacho', $bodega->despacho) == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección 2: Producto --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2 fw-semibold">Producto</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo de batería <span class="text-danger">*</span></label>
                    <select name="tipobateria" class="form-select" required>
                        <option value="">Seleccionar…</option>
                        @foreach (Bodega::TIPOS_BATERIA as $t)
                            <option value="{{ $t }}" @selected(old('tipobateria', $bodega->tipobateria) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contenedor <span class="text-danger">*</span></label>
                    <input type="text" name="contenedor" class="form-control" required value="{{ old('contenedor', $bodega->contenedor) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" step="any" name="cantidad" class="form-control" required
                           value="{{ old('cantidad', $bodega->cantidad) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unidad</label>
                    <select name="unidad" class="form-select">
                        <option value="">Seleccionar…</option>
                        @foreach ($unidades as $u)
                            <option value="{{ $u }}" @selected(old('unidad', $bodega->unidad) === $u)>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Observación</label>
                    <input type="text" name="observacion" class="form-control" value="{{ old('observacion', $bodega->observacion) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Sección 3: Motivo y Partida --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2 fw-semibold">Motivo y Partida</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Motivo</label>
                    <select name="motivo" class="form-select">
                        <option value="">Seleccionar…</option>
                        @foreach ($motivos as $m)
                            <option value="{{ $m }}" @selected(old('motivo', $bodega->motivo) === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Partida</label>
                    <input type="text" name="partida" class="form-control" value="{{ old('partida', $bodega->partida) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Sección 4: Destinatario --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2 fw-semibold">Destinatario</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Razón Social</label>
                    <input type="text" name="nombreDestinatario" class="form-control" value="{{ old('nombreDestinatario', $bodega->nombreDestinatario) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">RUC</label>
                    <input type="text" name="rucDestinatario" class="form-control" value="{{ old('rucDestinatario', $bodega->rucDestinatario) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Llegada</label>
                    <input type="text" name="llegada" class="form-control" value="{{ old('llegada', $bodega->llegada) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Sección 5: Transportista --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white py-2 fw-semibold">Transportista</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Razón Social</label>
                    <select name="nombreTransportista" id="nombreTransportista" class="form-select">
                        <option value="">Seleccionar…</option>
                        @foreach ($transportistas as $t)
                            <option value="{{ $t }}" @selected(old('nombreTransportista', $bodega->nombreTransportista) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">RUC</label>
                    <input type="text" name="transportistaRuc" class="form-control" value="{{ old('transportistaRuc', $bodega->transportistaRuc) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Placa</label>
                    <input type="text" name="placaTransportista" class="form-control" value="{{ old('placaTransportista', $bodega->placaTransportista) }}">
                </div>
            </div>
        </div>
    </div>
</form>
