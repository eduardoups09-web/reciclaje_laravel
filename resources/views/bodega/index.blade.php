@extends('layouts.app')
@section('titulo', 'Bodega')

@section('contenido')
@php use App\Models\Bodega; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-truck text-success"></i> Bodega · Despachos</h3>
    <a href="{{ route('bodega.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('bodega.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha</label>
            <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Tipo de batería</label>
            <select name="tipo" class="form-select">
                <option value="">Todos</option>
                @foreach (Bodega::TIPOS_BATERIA as $t)
                    <option value="{{ $t }}" @selected(($filtros['tipo'] ?? '') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Grupo</label>
            <select name="grupo" class="form-select">
                <option value="">Todos</option>
                @foreach (Bodega::GRUPOS as $g)
                    <option value="{{ $g }}" @selected(($filtros['grupo'] ?? '') === $g)>Grupo {{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Turno</label>
            <select name="turno" class="form-select">
                <option value="">Todos</option>
                @foreach (Bodega::TURNOS as $t)
                    <option value="{{ $t }}" @selected(($filtros['turno'] ?? '') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Buscar</label>
            <input type="text" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" class="form-control"
                   placeholder="contenedor, destinatario…">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('bodega.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">{{ number_format($registros->total()) }} registro(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th><th>Grupo</th><th>Turno</th><th>Tipo</th><th>Contenedor</th>
                    <th class="text-end">Cantidad</th><th>Unidad</th>
                    <th>Consec.</th><th>Despacho</th><th>Transportista</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($registros as $r)
                <tr>
                    <td>{{ $r->fechainicio }}</td>
                    <td>@if($r->grupo)<span class="badge bg-secondary">G{{ $r->grupo }}</span>@endif</td>
                    <td>{{ $r->turno }}</td>
                    <td class="small">{{ $r->tipobateria }}</td>
                    <td>{{ $r->contenedor }}</td>
                    <td class="text-end">{{ $r->cantidad }}</td>
                    <td>{{ $r->unidad }}</td>
                    <td>{{ $r->consecutivo }}</td>
                    <td>{{ $r->despacho }}</td>
                    <td class="small text-muted">{{ $r->nombreTransportista }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('bodega.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="{{ route('bodega.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $registros->links() }}</div>
@endsection
