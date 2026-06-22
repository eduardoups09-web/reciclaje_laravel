@extends('layouts.app')
@section('titulo', 'Movimientos')

@section('contenido')
@php
    use App\Models\Movimiento;
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
              7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-table text-success"></i> Movimientos por turno</h3>
</div>
<p class="text-muted">Datos agrupados por <strong>fecha · grupo · turno</strong>. Entra al detalle de un turno para ver el consolidado de todas las tablas (producción, insumos, ingresos, bodega, balance y calidad).</p>

{{-- Filtros --}}
<form method="get" action="{{ route('movimientos.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                <option value="">Todos</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" @selected(($filtros['anio ?? '') == $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select">
                <option value="">Todos</option>
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

<div class="text-muted small mb-2">{{ number_format($registros->total()) }} turno(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Turno</th><th>Estado</th>
                    <th class="text-end">Consolidado</th>
                </tr>
            </thead>
            <tbody>
            @php $porFecha = collect($registros->items())->groupBy('fecha'); @endphp
            @forelse ($porFecha as $fecha => $filasFecha)
                {{-- Encabezado de FECHA --}}
                <tr class="table-success">
                    <td colspan="3" class="fw-bold">
                        <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
                    </td>
                </tr>
                @foreach ($filasFecha->groupBy('grupo') as $grupo => $filasGrupo)
                    {{-- Subencabezado de GRUPO --}}
                    <tr class="table-light">
                        <td colspan="3" class="ps-4 text-muted small fw-semibold">
                            <i class="bi bi-people"></i> Grupo {{ $grupo }}
                        </td>
                    </tr>
                    {{-- Filas de TURNO --}}
                    @foreach ($filasGrupo as $r)
                        <tr>
                            <td class="ps-5">{{ $r->turno }}</td>
                            <td>
                                @if ($r->status_id == 3)
                                    <span class="badge bg-success">Aprobado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Registrado</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('movimientos.show', ['fecha' => $r->fecha, 'grupo' => $r->grupo, 'turno' => $r->turno]) }}"
                                   class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i> Ver detalle</a>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $registros->links() }}</div>
@endsection
