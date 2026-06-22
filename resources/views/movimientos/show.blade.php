@extends('layouts.app')
@section('titulo', 'Detalle de movimiento')

@section('contenido')
@php use App\Models\Movimiento; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-table text-success"></i> Detalle del turno</h3>
    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

{{-- Cabecera: fecha / grupo / turno --}}
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-4">
        <div><div class="small text-uppercase text-muted">Fecha</div><div class="fs-5 fw-semibold">{{ $m->fecha }}</div></div>
        <div><div class="small text-uppercase text-muted">Grupo</div><div class="fs-5 fw-semibold">Grupo {{ $m->grupo }}</div></div>
        <div><div class="small text-uppercase text-muted">Turno</div><div class="fs-5 fw-semibold">{{ $m->turno }}</div></div>
        <div><div class="small text-uppercase text-muted">Estado</div><div class="fs-5 fw-semibold">{{ $m->status_id == 3 ? 'Aprobado' : 'Registrado' }}</div></div>
    </div>
</div>

{{-- Secciones desglosadas --}}
<div class="row g-3">
    @foreach (Movimiento::SECCIONES as $titulo => $campos)
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">{{ $titulo }}</div>
                <ul class="list-group list-group-flush">
                    @foreach ($campos as $col => $etiqueta)
                        @php $val = $m->$col; @endphp
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">{{ $etiqueta }}</span>
                            <span class="fw-semibold {{ (is_numeric($val) && $val < 0) ? 'text-danger' : '' }}">
                                @if (is_null($val) || $val === '')
                                    —
                                @elseif (is_numeric($val))
                                    {{ number_format($val, (floor($val) == $val ? 0 : 2)) }}
                                @else
                                    {{ $val }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@endsection
