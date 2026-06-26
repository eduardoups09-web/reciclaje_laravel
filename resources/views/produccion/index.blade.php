@extends('layouts.app')
@section('titulo', 'Producción')

@section('contenido')
@php use App\Models\Salida; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-box-seam text-success"></i> Producción · Salidas</h3>
    <a href="{{ route('produccion.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('produccion.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                <option value="">Todos</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" @selected(($filtros['anio'] ?? '') == $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select">
                <option value="">Todos</option>
                @foreach (['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $key => $nombreMes)
                    <option value="{{ $key + 1 }}" @selected(($filtros['mes'] ?? '') == ($key + 1))>{{ $nombreMes }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('produccion.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">{{ number_format($registros->total()) }} registro(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th><th>Grupo</th><th>Turno</th>
                    <th class="text-end">Metálico</th>
                    <th class="text-end">Rejilla</th>
                    <th class="text-end">Met. fino</th>
                    <th class="text-end">Pasta desulf.</th>
                    <th class="text-end">Pasta sin</th>
                    <th>Usuario</th>
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
                        <form method="post" action="{{ route('produccion.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $registros->links() }}
</div>
@endsection
