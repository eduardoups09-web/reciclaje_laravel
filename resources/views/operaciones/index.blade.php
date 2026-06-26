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
        <div class="col-md-3">
            <label class="form-label small">Turno</label>
            <select name="turno" class="form-select">
                <option value="">Todos</option>
                <option value="Diurno" @selected($filtros['turno'] === 'Diurno')>Diurno</option>
                <option value="Nocturno" @selected($filtros['turno'] === 'Nocturno')>Nocturno</option>
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
    <a href="{{ route("$recurso.create") }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo
    </a>
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
                                <td>{{ $r->fecha }}</td>
                                <td><span class="badge bg-secondary">G{{ $r->grupo }}</span></td>
                                <td>{{ $r->turno }}</td>
                                <td>{{ $r->status_id == 3 ? 'Aprobado' : 'Registrado' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('movimiento-detalle.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
                                    <a href="{{ route('mpnacional.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
                                    <a href="{{ route('mpimport.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
                            <th class="text-end">Carbonato de sodio</th><th class="text-end">Cal</th>
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
                                <td class="text-end">{{ !is_null($r->cal) ? number_format($r->cal) : '—' }}</td>
                                <td class="small text-muted">{{ $r->usernameinsumo }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('insumos.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="{{ route('insumos.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                @break

                @case('produccion')
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Grupo</th><th>Turno</th>
                            <th class="text-end">Metálico</th><th class="text-end">Rejilla</th>
                            <th class="text-end">Met. fino</th><th class="text-end">Pasta desulf.</th>
                            <th class="text-end">Pasta sin</th><th>Usuario</th>
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
                                <td class="small text-muted">{{ $r->usernameproduccion }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('produccion.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="{{ route('produccion.destroy', $r) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">Sin registros.</td></tr>
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
                                    <a href="{{ route('calidad.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
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
@endsection
