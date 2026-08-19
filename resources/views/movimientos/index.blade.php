@extends('layouts.app')
@section('titulo', 'Movimientos')

@section('contenido')
<style>
    #tabla-movimientos td,
    #tabla-movimientos th {
        border: 1px solid #dee2e6 !important;
    }
</style>

@php
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
              7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $estados = [1 => 'Abierto', 2 => 'Cerrado', 4 => 'Aprobado'];
    $all = $all ?? $registros->getCollection();
    $f = fn($v) => number_format($v ?? 0, 0, ',', '.');
    $f2 = fn($v) => number_format($v ?? 0, 2, ',', '.');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-table text-success"></i> Control de Movimientos</h3>
</div>

<form method="get" action="{{ route('movimientos.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select" required>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" @selected(($filtros['anio'] ?? '') == $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select" required>
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}" @selected(($filtros['mes'] ?? '') == $num)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">{{ $all->count() }} turno(s) encontrados.</div>

@if ($all->isNotEmpty())
<div class="card shadow-sm">
    <div class="table-responsive">
        <table id="tabla-movimientos" class="table table-hover table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center text-white fw-semibold" style="background-color:#28a745;" colspan="4">Principal</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#17a2b8;" colspan="2">Baterías Nacionales</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#fd7e14;" colspan="2">Baterías Importadas</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#dc3545;" colspan="3">MP Importada</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#e0a800;" colspan="1">Insumos</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#17a2b8;" colspan="9">Producción</th>
                    <th class="text-center text-white fw-semibold" style="background-color:#6f42c1;" colspan="2">Calidad</th>
                </tr>
                <tr>
                    <th style="background-color:#d4edda;">Fecha</th>
                    <th class="text-center" style="background-color:#d4edda;">Turno</th>
                    <th class="text-center" style="background-color:#d4edda;">Grupo</th>
                    <th class="text-center" style="background-color:#d4edda;">Estado</th>
                    <th class="text-end" style="background-color:#cfe2f3;">Bat. Nac.</th>
                    <th style="background-color:#cfe2f3;">Tipo Bat. Nac.</th>
                    <th class="text-end" style="background-color:#fce4d6;">Bat. Imp.</th>
                    <th style="background-color:#fce4d6;">Tipo Bat. Imp.</th>
                    <th class="text-end" style="background-color:#f8d7da;">Met. Imp.</th>
                    <th class="text-end" style="background-color:#f8d7da;">Pasta</th>
                    <th class="text-end" style="background-color:#f8d7da;">Placas</th>
                    <th class="text-end" style="background-color:#fff3cd;">Carb. Sodio</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Metálico</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Rejilla</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Met. Fino</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Pasta Des.</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Pasta Sin</th>
                    <th class="text-end" style="background-color:#d1ecf1;">PP</th>
                    <th class="text-end" style="background-color:#d1ecf1;">ABS</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Sep.</th>
                    <th class="text-end" style="background-color:#d1ecf1;">Desc.</th>
                    <th class="text-end" style="background-color:#e2d5f1;">%Azufre</th>
                    <th class="text-end" style="background-color:#e2d5f1;">%Humedad</th>
                    <th class="text-center" style="background-color:#d4edda;">Acciones</th>
                </tr>
                <tr class="table-warning fw-bold">
                    <td colspan="4">TOTALES</td>
                    <td class="text-end">{{ $f($all->sum('pesobateria')) }}</td>
                    <td></td>
                    <td class="text-end">{{ $f($all->sum('pesobateriaimport')) }}</td>
                    <td></td>
                    <td class="text-end">{{ $f($all->sum('metalicoimport')) }}</td>
                    <td class="text-end">{{ $f($all->sum('pastaimport')) }}</td>
                    <td class="text-end">{{ $f($all->sum('placasimport')) }}</td>
                    <td class="text-end">{{ $f($all->sum('carbonatoSodio')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_metalico')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_rejilla')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_metalicofino')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_pastadesulfurada')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_pastasin')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_polipropilenokg')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_abskg')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_separadorkg')) }}</td>
                    <td class="text-end">{{ $f($all->sum('salidas_descargas')) }}</td>
                    <td class="text-end">{{ $promedioCalidad ? $f2($promedioCalidad->azufre) : '0.00' }}</td>
                    <td class="text-end">{{ $promedioCalidad ? $f2($promedioCalidad->humedad) : '0.00' }}</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
            @forelse ($registros as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->fecha }}</td>
                    <td class="text-center">{{ $r->turno }}</td>
                    <td class="text-center"><span class="badge bg-secondary">G{{ $r->grupo }}</span></td>
                    <td class="text-center">
                        @php
                            $estiloStatus = match($r->status_id) {
                                3 => 'text-success',
                                2 => 'text-warning',
                                default => 'text-dark',
                            };
                        @endphp
                        <span class="{{ $estiloStatus }}">{{ $estados[$r->status_id] ?? 'N/A' }}</span>
                    </td>
                    <td class="text-end">{{ $f($r->pesobateria) }}</td>
                    <td>{{ $r->bateriatipo }}</td>
                    <td class="text-end">{{ $f($r->pesobateriaimport) }}</td>
                    <td>{{ $r->bateriatipoimport }}</td>
                    <td class="text-end">{{ $f($r->metalicoimport) }}</td>
                    <td class="text-end">{{ $f($r->pastaimport) }}</td>
                    <td class="text-end">{{ $f($r->placasimport) }}</td>
                    <td class="text-end">{{ $f($r->carbonatoSodio) }}</td>
                    <td class="text-end fw-bold">{{ $f($r->salidas_metalico) }}</td>
                    <td class="text-end">{{ $f($r->salidas_rejilla) }}</td>
                    <td class="text-end">{{ $f($r->salidas_metalicofino) }}</td>
                    <td class="text-end">{{ $f($r->salidas_pastadesulfurada) }}</td>
                    <td class="text-end">{{ $f($r->salidas_pastasin) }}</td>
                    <td class="text-end">{{ $f($r->salidas_polipropilenokg) }}</td>
                    <td class="text-end">{{ $f($r->salidas_abskg) }}</td>
                    <td class="text-end">{{ $f($r->salidas_separadorkg) }}</td>
                    <td class="text-end">{{ $f($r->salidas_descargas) }}</td>
                    <td class="text-end text-white fw-bold" style="background-color: {{ ($r->calidad_azufre > 0.99) ? '#dc3545' : '#28a745' }};">
                        {{ $f2($r->calidad_azufre) }}
                    </td>
                    <td class="text-end">{{ $f2($r->calidad_humedad) }}</td>
                    <td class="text-center text-nowrap">
                        <form method="post" action="{{ route('movimientos.destroy') }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar todos los registros de esta fecha y turno?');">
                            @csrf
                            <input type="hidden" name="fecha" value="{{ $r->fecha }}">
                            <input type="hidden" name="turno" value="{{ $r->turno }}">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="24" class="text-center text-muted py-4">Sin registros. Seleccione año y mes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
    <div class="text-center text-muted py-4">Sin registros. Seleccione año y mes.</div>
@endif
@endsection
