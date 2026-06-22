@extends('layouts.app')
@section('titulo', 'Inicio')

@section('contenido')
<h3 class="mb-4"><i class="bi bi-speedometer2 text-success"></i> Panel principal</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card text-bg-success shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-uppercase opacity-75">Salidas hoy</div>
                    <div class="fs-2 fw-bold">{{ number_format($salidasHoy) }}</div>
                </div>
                <i class="bi bi-calendar-day fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm"><div class="card-body">
            <div class="small text-uppercase text-muted">Total salidas</div>
            <div class="fs-2 fw-bold">{{ number_format($totalSalidas) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm"><div class="card-body">
            <div class="small text-uppercase text-muted">Movimientos</div>
            <div class="fs-2 fw-bold">{{ number_format($totalMovimientos) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm"><div class="card-body">
            <div class="small text-uppercase text-muted">Pasta desulf. (7 días)</div>
            <div class="fs-2 fw-bold">{{ number_format($pastaSemana) }}</div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm"><div class="card-body">
    <h5 class="card-title"><i class="bi bi-box-seam text-success"></i> Producción</h5>
    <p class="text-muted">Registra y consulta las salidas de producción (productos recuperados por turno y grupo).</p>
    <a href="{{ route('produccion.index') }}" class="btn btn-success"><i class="bi bi-list-ul"></i> Ver registros</a>
    <a href="{{ route('produccion.create') }}" class="btn btn-outline-success"><i class="bi bi-plus-lg"></i> Nuevo registro</a>
</div></div>
@endsection
