@extends('layouts.app')
@section('titulo', 'Movimientos')

@section('contenido')
@php
    use App\Models\Movimiento;
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
              7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

    $secciones = Movimiento::SECCIONES;

    // Contar columnas totales
    $totalCols = 4; // Fecha, Grupo, Turno, Estado
    foreach ($secciones as $campos) {
        $totalCols += count($campos);
    }

    // Colores por sección
    $coloresSeccion = [
        'Producción (salidas)'          => 'text-primary',
        'Materia prima / Importación'   => 'text-info',
        'Insumos'                       => 'text-warning',
        'Ingresos'                      => 'text-success',
        'Bodega y balance'              => 'text-secondary',
        'Saldos'                        => 'text-dark',
        'Calidad'                       => 'text-danger',
    ];

    $bgSeccion = [
        'Producción (salidas)'          => 'table-primary',
        'Materia prima / Importación'   => 'table-info',
        'Insumos'                       => 'table-warning',
        'Ingresos'                      => 'table-success',
        'Bodega y balance'              => 'table-secondary',
        'Saldos'                        => 'table-dark',
        'Calidad'                       => 'table-danger',
    ];

    function fmtMov($val) {
        if (is_null($val) || $val === '') return '—';
        if (is_numeric($val)) {
            $fmt = ($val == floor($val) && abs($val) < 10000) ? number_format($val, 0) : number_format($val, 2);
            return $fmt;
        }
        return $val;
    }
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-table text-success"></i> Movimientos consolidados</h3>
</div>
<p class="text-muted">Datos agrupados por <strong>fecha · grupo · turno</strong> con el consolidado de todas las tablas.</p>

{{-- Filtros --}}
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

<div class="text-muted small mb-2">{{ count($registros) }} turno(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-sm align-middle mb-0" style="font-size: 0.78rem;">
            <thead>
                {{-- Fila 1: Encabezados de sección --}}
                <tr class="table-light">
                    <th rowspan="2" class="text-center" style="min-width:90px;">Fecha</th>
                    <th rowspan="2" class="text-center" style="min-width:55px;">Grupo</th>
                    <th rowspan="2" class="text-center" style="min-width:80px;">Turno</th>
                    <th rowspan="2" class="text-center" style="min-width:80px;">Estado</th>
                    @foreach ($secciones as $titulo => $campos)
                        <th colspan="{{ count($campos) }}" class="text-center {{ $coloresSeccion[$titulo] ?? '' }} fw-bold" style="border-left: 2px solid #dee2e6;">
                            {{ $titulo }}
                        </th>
                    @endforeach
                </tr>
                {{-- Fila 2: Nombres de columna --}}
                <tr class="table-light">
                    @foreach ($secciones as $titulo => $campos)
                        @foreach ($campos as $col => $etiqueta)
                            <th class="text-center {{ $coloresSeccion[$titulo] ?? '' }}" style="min-width:80px; border-left: 1px solid #dee2e6; font-size:0.7rem;">
                                {{ $etiqueta }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @php $porFecha = $registros->groupBy('fecha'); @endphp
            @forelse ($porFecha as $fecha => $filasFecha)
                {{-- Encabezado de FECHA --}}
                <tr class="table-success">
                    <td colspan="{{ $totalCols }}" class="fw-bold">
                        <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
                    </td>
                </tr>
                @foreach ($filasFecha->groupBy('grupo') as $grupo => $filasGrupo)
                    {{-- Subencabezado de GRUPO --}}
                    <tr class="table-light">
                        <td colspan="{{ $totalCols }}" class="ps-4 text-muted small fw-semibold">
                            <i class="bi bi-people"></i> Grupo {{ $grupo }}
                        </td>
                    </tr>
                    {{-- Filas de TURNO --}}
                    @foreach ($filasGrupo as $r)
                        <tr>
                            <td class="fw-semibold">{{ $r->fecha }}</td>
                            <td class="text-center"><span class="badge bg-secondary">G{{ $r->grupo }}</span></td>
                            <td>{{ $r->turno }}</td>
                            <td>
                                @if ($r->status_id == 3)
                                    <span class="badge bg-success">Aprobado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Registrado</span>
                                @endif
                            </td>
                            @foreach ($secciones as $titulo => $campos)
                                @foreach ($campos as $col => $etiqueta)
                                    <td class="text-end {{ $coloresSeccion[$titulo] ?? '' }}" style="border-left: 1px solid #dee2e6;">
                                        {{ fmtMov($r->$col) }}
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr><td colspan="{{ $totalCols }}" class="text-center text-muted py-4">Sin registros. Seleccione año y mes para consultar.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
