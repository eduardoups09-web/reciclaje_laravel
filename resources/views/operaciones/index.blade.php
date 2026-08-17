@extends('layouts.app')
@section('titulo', 'Operaciones')

@section('contenido')
@php
use App\Models\Salida;
use App\Models\AnalisisCalidad;
use App\Models\MpImport;
use App\Models\MpNacional;
use App\Models\Insumo;
use App\Models\MovimientoDetalle;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-gear text-success"></i> Operaciones</h3>
</div>

{{-- Pestañas --}}
<ul class="nav nav-tabs mb-3">
    @foreach ([
        'movdetalle' => 'Mov. Detalle',
        'mpnacional' => 'MP Nacional',
        'mpimport' => 'MP Importación',
        'insumos' => 'Insumos',
        'produccion' => 'Producción',
        'calidad' => 'Calidad',
    ] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $tab === $key ? 'active fw-bold' : '' }}"
               href="{{ request()->fullUrlWithQuery(['tab' => $key, 'page' => null]) }}">
               {{ $label }}
            </a>
        </li>
    @endforeach
</ul>

{{-- Filtros --}}
<form method="get" action="{{ route('operaciones.index') }}" class="card card-body shadow-sm mb-3">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha</label>
            <input type="date" name="fecha" value="{{ $filtros['fecha'] }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Turno</label>
            <select name="turno" class="form-select">
                <option value="">Todos</option>
                <option value="Diurno" @selected($filtros['turno'] === 'Diurno')>Diurno</option>
                <option value="Nocturno" @selected($filtros['turno'] === 'Nocturno')>Nocturno</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Grupo</label>
            <select name="grupo" class="form-select">
                <option value="">Todos</option>
                <option value="1" @selected(($filtros['grupo'] ?? '') === '1')>Grupo 1</option>
                <option value="2" @selected(($filtros['grupo'] ?? '') === '2')>Grupo 2</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('operaciones.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

{{-- Botón Nuevo + conteo --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small">{{ number_format($registros->total()) }} registro(s) encontrados.</div>
    @if ($tab === 'movdetalle')
        <button onclick="abrirModalCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @elseif ($tab === 'mpnacional')
        <button onclick="abrirModalMpNacionalCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @elseif ($tab === 'mpimport')
        <button onclick="abrirModalMpImportCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @elseif ($tab === 'insumos')
        <button onclick="abrirModalInsumoCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @elseif ($tab === 'produccion')
        <button onclick="abrirModalProduccionCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @elseif ($tab === 'calidad')
        <button onclick="abrirModalCalidadCrear()" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </button>
    @else
        <a href="{{ route("$recurso.create") }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo
        </a>
    @endif
</div>

{{-- Tabla dinámica --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            @switch($tab)
                @case('movdetalle')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th><th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r->fecha }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->grupo }}</span></td>
                                <td>{{ $r->turno }}</td>
                                <td>
                                    @php
                                        $estiloEstado = match($r->status_id) {
                                            1 => 'bg-warning text-dark',
                                            2 => 'bg-primary text-white',
                                            3 => 'bg-success text-white',
                                            default => 'bg-secondary text-white',
                                        };
                                    @endphp
                                    <span class="badge {{ $estiloEstado }}">{{ MovimientoDetalle::ESTADOS[$r->status_id] ?? 'Registrado' }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('movimiento-detalle.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('mpnacional')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th>
                            <th>Tipo batería</th><th class="text-end">Peso batería</th>
                            <th>Usuario</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td>{{ $r->fechanacional }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->gruponacional }}</span></td>
                                <td>{{ $r->turnonacional }}</td>
                                <td>{{ $r->bateriatipo ?? '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pesobateria) ? number_format($r->pesobateria) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernamenacional }}</td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalMpNacionalEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('mpnacional.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('mpimport')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th>
                            <th>Tipo batería</th><th class="text-end">Peso batería</th>
                            <th class="text-end">Metálico</th><th class="text-end">Pasta</th><th class="text-end">Placas</th>
                            <th>Usuario</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td>{{ $r->fechaimport }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->grupoimport }}</span></td>
                                <td>{{ $r->turnoimport }}</td>
                                <td>{{ $r->bateriatipoimport ?? '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pesobateriaimport) ? number_format($r->pesobateriaimport) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->metalicoimport) ? number_format($r->metalicoimport) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pastaimport) ? number_format($r->pastaimport) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->placasimport) ? number_format($r->placasimport) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernameimport }}</td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalMpImportEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('mpimport.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('insumos')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th>
                            <th class="text-end">Carbonato de sodio</th>
                            <th>Usuario</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td>{{ $r->fecha }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->grupoinsumo }}</span></td>
                                <td>{{ $r->turnoinsumo }}</td>
                                <td class="text-end">{{ !is_null($r->carbonatoSodio) ? number_format($r->carbonatoSodio) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernameinsumo }}</td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalInsumoEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('insumos.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('produccion')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th>
                            <th class="text-end">Metálico</th><th class="text-end">Rejilla</th>
                            <th class="text-end">Met. fino</th><th class="text-end">Pasta desulf.</th>
                            <th class="text-end">Pasta sin</th><th class="text-end">Polipropileno</th>
                            <th class="text-end">ABS</th><th class="text-end">Separador</th>
                            <th class="text-end">Descargas</th><th>Usuario</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td>{{ $r->fechasalida }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->gruposalida }}</span></td>
                                <td>{{ $r->turnosalida }}</td>
                                <td class="text-end">{{ !is_null($r->metalico) ? number_format($r->metalico) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->rejilla) ? number_format($r->rejilla) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->metalicofino) ? number_format($r->metalicofino) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pastadesulfurada) ? number_format($r->pastadesulfurada) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pastasin) ? number_format($r->pastasin) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->polipropilenokg) ? number_format($r->polipropilenokg) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->abskg) ? number_format($r->abskg) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->separadorkg) ? number_format($r->separadorkg) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->descargas) ? number_format($r->descargas) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernameproduccion }}</td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalProduccionEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('produccion.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="14" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('calidad')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Hora</th><th>Turno</th><th>Reactor</th><th>Filtro</th>
                            <th class="text-end">PI</th>
                            <th class="text-end">PF</th>
                            <th class="text-end">Temp. (°C)</th><th class="text-end">pH</th>
                            <th class="text-end">Azufre (%)</th>
                            <th class="text-end">Humedad (%)</th>
                            <th>Usuario</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $r)
                            <tr>
                                <td>{{ $r->fecha }}</td>
                                <td>{{ $r->hora_corta }}</td>
                                <td>{{ $r->turnocalidad }}</td>
                                <td>
                                    {{ $r->reactores_nombres ?: '—' }}
                                </td>
                                <td>{{ $r->filtro }}</td>
                                <td class="text-end">{{ !is_null($r->pi) ? number_format($r->pi, 2) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->pf) ? number_format($r->pf, 2) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->temperatura) ? number_format($r->temperatura, 2) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->ph) ? number_format($r->ph, 2) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->azufre) ? number_format($r->azufre, 2) : '—' }}</td>
                                <td class="text-end">{{ !is_null($r->humedad) ? number_format($r->humedad, 2) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernamecalidad }}</td>
                                <td class="text-end text-nowrap">
                                    <button onclick="abrirModalCalidadEditar({{ $r->id }})" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                    <form method="post" action="{{ route('calidad.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break
            @endswitch
        </table>
    </div>
</div>

<div class="mt-3">{{ $registros->links() }}</div>

{{-- Modal para Movimiento Detalle --}}
@if ($tab === 'movdetalle')
<div class="modal fade" id="modalMovDetalle" tabindex="-1" aria-labelledby="modalMovDetalleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMovDetalleLabel">Movimiento Detalle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalMovDetalleBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarMovDetalle">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

@endif

@if ($tab === 'mpnacional')
<div class="modal fade" id="modalMpNacional" tabindex="-1" aria-labelledby="modalMpNacionalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMpNacionalLabel">MP Nacional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalMpNacionalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarMpNacional">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'mpimport')
<div class="modal fade" id="modalMpImport" tabindex="-1" aria-labelledby="modalMpImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMpImportLabel">MP Importación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalMpImportBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarMpImport">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'insumos')
<div class="modal fade" id="modalInsumo" tabindex="-1" aria-labelledby="modalInsumoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInsumoLabel">Insumos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalInsumoBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarInsumo">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'produccion')
<div class="modal fade" id="modalProduccion" tabindex="-1" aria-labelledby="modalProduccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProduccionLabel">Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalProduccionBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarProduccion">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'calidad')
<div class="modal fade" id="modalCalidad" tabindex="-1" aria-labelledby="modalCalidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCalidadLabel">Análisis de Calidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalCalidadBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarCalidad">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if ($tab === 'movdetalle')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalMovDetalle'));
    const modalBody = document.getElementById('modalMovDetalleBody');
    const modalTitle = document.getElementById('modalMovDetalleLabel');
    const btnGuardar = document.getElementById('btnGuardarMovDetalle');

    window.abrirModalCrear = function() {
        modalTitle.textContent = 'Nuevo movimiento detalle';
        btnGuardar.onclick = guardarCrear;
        cargarForm('{{ route("movimiento-detalle.create") }}' + window.location.search);
    };

    window.abrirModalEditar = function(id) {
        modalTitle.textContent = 'Editar movimiento #' + id;
        btnGuardar.onclick = () => guardarEditar(id);
        cargarForm('/movimiento-detalle/' + id + '/edit');
    };

    function cargarForm(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => {
                modalBody.innerHTML = html;
                initSwitches();
            })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function initSwitches() {
        var swD = document.getElementById('switchDiurno');
        var swN = document.getElementById('switchNocturno');
        var gD = document.getElementById('grupoDiurno');
        var gN = document.getElementById('grupoNocturno');
        if (swD && gD) swD.addEventListener('change', function() { gD.disabled = !this.checked; if (!this.checked) gD.value = ''; });
        if (swN && gN) swN.addEventListener('change', function() { gN.disabled = !this.checked; if (!this.checked) gN.value = ''; });
    }

    function guardarCrear() {
        enviarForm('{{ route("movimiento-detalle.store") }}', 'POST');
    }

    function guardarEditar(id) {
        const form = document.getElementById('formMovDetalle');
        const method = form.querySelector('input[name="_method"]');
        enviarForm('/movimiento-detalle/' + id, method ? method.value : 'PUT');
    }

    function enviarForm(url, method) {
        const form = document.getElementById('formMovDetalle');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErrores(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErrores({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErrores(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif

@if ($tab === 'mpnacional')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalMpNacional'));
    const modalBody = document.getElementById('modalMpNacionalBody');
    const modalTitle = document.getElementById('modalMpNacionalLabel');
    const btnGuardar = document.getElementById('btnGuardarMpNacional');

    window.abrirModalMpNacionalCrear = function() {
        modalTitle.textContent = 'Nueva MP Nacional';
        btnGuardar.onclick = guardarMpNacionalCrear;
        cargarFormMpNacional('{{ route("mpnacional.create") }}' + window.location.search);
    };

    window.abrirModalMpNacionalEditar = function(id) {
        modalTitle.textContent = 'Editar MP Nacional #' + id;
        btnGuardar.onclick = () => guardarMpNacionalEditar(id);
        cargarFormMpNacional('/mpnacional/' + id + '/edit');
    };

    function cargarFormMpNacional(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => { modalBody.innerHTML = html; })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarMpNacionalCrear() {
        enviarFormMpNacional('{{ route("mpnacional.store") }}', 'POST');
    }

    function guardarMpNacionalEditar(id) {
        const form = document.getElementById('formMpNacional');
        const method = form.querySelector('input[name="_method"]');
        enviarFormMpNacional('/mpnacional/' + id, method ? method.value : 'PUT');
    }

    function enviarFormMpNacional(url, method) {
        const form = document.getElementById('formMpNacional');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErroresMpNacional(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErroresMpNacional({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErroresMpNacional(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif

@if ($tab === 'mpimport')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalMpImport'));
    const modalBody = document.getElementById('modalMpImportBody');
    const modalTitle = document.getElementById('modalMpImportLabel');
    const btnGuardar = document.getElementById('btnGuardarMpImport');

    window.abrirModalMpImportCrear = function() {
        modalTitle.textContent = 'Nueva MP Importación';
        btnGuardar.onclick = guardarMpImportCrear;
        cargarFormMpImport('{{ route("mpimport.create") }}' + window.location.search);
    };

    window.abrirModalMpImportEditar = function(id) {
        modalTitle.textContent = 'Editar MP Importación #' + id;
        btnGuardar.onclick = () => guardarMpImportEditar(id);
        cargarFormMpImport('/mpimport/' + id + '/edit');
    };

    function cargarFormMpImport(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => { modalBody.innerHTML = html; })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarMpImportCrear() {
        enviarFormMpImport('{{ route("mpimport.store") }}', 'POST');
    }

    function guardarMpImportEditar(id) {
        const form = document.getElementById('formMpImport');
        const method = form.querySelector('input[name="_method"]');
        enviarFormMpImport('/mpimport/' + id, method ? method.value : 'PUT');
    }

    function enviarFormMpImport(url, method) {
        const form = document.getElementById('formMpImport');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErroresMpImport(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErroresMpImport({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErroresMpImport(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif

@if ($tab === 'insumos')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalInsumo'));
    const modalBody = document.getElementById('modalInsumoBody');
    const modalTitle = document.getElementById('modalInsumoLabel');
    const btnGuardar = document.getElementById('btnGuardarInsumo');

    window.abrirModalInsumoCrear = function() {
        modalTitle.textContent = 'Nuevo insumo';
        btnGuardar.onclick = guardarInsumoCrear;
        cargarFormInsumo('{{ route("insumos.create") }}' + window.location.search);
    };

    window.abrirModalInsumoEditar = function(id) {
        modalTitle.textContent = 'Editar insumo #' + id;
        btnGuardar.onclick = () => guardarInsumoEditar(id);
        cargarFormInsumo('/insumos/' + id + '/edit');
    };

    function cargarFormInsumo(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => { modalBody.innerHTML = html; })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarInsumoCrear() {
        enviarFormInsumo('{{ route("insumos.store") }}', 'POST');
    }

    function guardarInsumoEditar(id) {
        const form = document.getElementById('formInsumo');
        const method = form.querySelector('input[name="_method"]');
        enviarFormInsumo('/insumos/' + id, method ? method.value : 'PUT');
    }

    function enviarFormInsumo(url, method) {
        const form = document.getElementById('formInsumo');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErroresInsumo(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErroresInsumo({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErroresInsumo(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif

@if ($tab === 'produccion')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalProduccion'));
    const modalBody = document.getElementById('modalProduccionBody');
    const modalTitle = document.getElementById('modalProduccionLabel');
    const btnGuardar = document.getElementById('btnGuardarProduccion');

    window.abrirModalProduccionCrear = function() {
        modalTitle.textContent = 'Nueva producción';
        btnGuardar.onclick = guardarProduccionCrear;
        cargarFormProduccion('{{ route("produccion.create") }}' + window.location.search);
    };

    window.abrirModalProduccionEditar = function(id) {
        modalTitle.textContent = 'Editar producción #' + id;
        btnGuardar.onclick = () => guardarProduccionEditar(id);
        cargarFormProduccion('/produccion/' + id + '/edit');
    };

    function cargarFormProduccion(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => { modalBody.innerHTML = html; })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarProduccionCrear() {
        enviarFormProduccion('{{ route("produccion.store") }}', 'POST');
    }

    function guardarProduccionEditar(id) {
        const form = document.getElementById('formSalida');
        const method = form.querySelector('input[name="_method"]');
        enviarFormProduccion('/produccion/' + id, method ? method.value : 'PUT');
    }

    function enviarFormProduccion(url, method) {
        const form = document.getElementById('formSalida');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErroresProduccion(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErroresProduccion({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErroresProduccion(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif

@if ($tab === 'calidad')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalCalidad'));
    const modalBody = document.getElementById('modalCalidadBody');
    const modalTitle = document.getElementById('modalCalidadLabel');
    const btnGuardar = document.getElementById('btnGuardarCalidad');

    window.abrirModalCalidadCrear = function() {
        modalTitle.textContent = 'Nuevo análisis de calidad';
        btnGuardar.onclick = guardarCalidadCrear;
        cargarFormCalidad('{{ route("calidad.create") }}' + window.location.search);
    };

    window.abrirModalCalidadEditar = function(id) {
        modalTitle.textContent = 'Editar análisis #' + id;
        btnGuardar.onclick = () => guardarCalidadEditar(id);
        cargarFormCalidad('/calidad/' + id + '/edit');
    };

    function cargarFormCalidad(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => {
                modalBody.innerHTML = html;
                modalBody.querySelectorAll('script').forEach(old => {
                    const s = document.createElement('script');
                    s.textContent = old.textContent;
                    old.replaceWith(s);
                });
            })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarCalidadCrear() {
        enviarFormCalidad('{{ route("calidad.store") }}', 'POST');
    }

    function guardarCalidadEditar(id) {
        const form = document.getElementById('formCalidad');
        const method = form.querySelector('input[name="_method"]');
        enviarFormCalidad('/calidad/' + id, method ? method.value : 'PUT');
    }

    function enviarFormCalidad(url, method) {
        const form = document.getElementById('formCalidad');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                modal.hide();
                location.reload();
            } else {
                mostrarErroresCalidad(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErroresCalidad({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErroresCalidad(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }
})();
</script>
@endif
@endpush
