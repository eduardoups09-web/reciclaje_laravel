@extends('layouts.app')
@section('titulo', 'Calidad')

@section('contenido')
@php use App\Models\AnalisisCalidad; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-droplet-half text-success"></i> Análisis de Calidad</h3>
    <a href="{{ route('calidad.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('calidad.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                <option value="">Todos</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" @selected(($filtros['anio'] ?? '') == $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select">
                <option value="">Todos</option>
                @foreach ([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num => $nombre)
                    <option value="{{ $num }}" @selected(($filtros['mes'] ?? '') == $num)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('calidad.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">{{ number_format($registros->total()) }} registro(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th><th>Hora</th><th>Turno</th><th>Reactor</th><th>Filtro</th>
                    <th class="text-end">PI</th>
                    <th class="text-end">PF</th>
                    <th class="text-end">Temp. (°C)</th>
                    <th class="text-end">pH</th>
                    <th class="text-end">Azufre (%)</th>
                    <th class="text-end">Humedad (%)</th>
                    <th>Usuario</th>
                    <th class="text-end">Acciones</th>
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
                        <form method="post" action="{{ route('calidad.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este análisis?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $registros->links() }}
</div>
@endsection
