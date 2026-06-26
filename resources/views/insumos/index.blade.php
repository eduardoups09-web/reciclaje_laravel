@extends('layouts.app')
@section('titulo', 'Insumos')

@section('contenido')
@php use App\Models\Insumo; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-droplet text-success"></i> Insumos</h3>
    <a href="{{ route('insumos.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

<form method="get" action="{{ route('insumos.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha</label>
            <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Turno</label>
            <select name="turno" class="form-select">
                <option value="">Todos</option>
                @foreach (Insumo::TURNOS as $t)
                    <option value="{{ $t }}" @selected(($filtros['turno'] ?? '') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('insumos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                    <th class="text-end">Carbonato de sodio</th>
                    <th class="text-end">Cal</th>
                    <th>Usuario</th>
                    <th class="text-end">Acciones</th>
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
                        <form method="post" action="{{ route('insumos.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $registros->links() }}</div>
@endsection
