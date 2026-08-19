@extends('layouts.app')
@section('titulo', 'Detalle de movimiento')

@section('contenido')
@php
    function fmtMvd($val) {
        if (is_null($val) || $val === '') return '—';
        if (is_numeric($val)) return number_format($val, ($val == floor($val) ? 0 : 2));
        return $val;
    }
    $estados = [1 => 'Abierto', 2 => 'Cerrado', 3 => 'Aprobado'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-table text-success"></i> Detalle del turno</h3>
    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-4">
        <div><div class="small text-uppercase text-muted">Fecha</div><div class="fs-5 fw-semibold">{{ $m->fecha }}</div></div>
        <div><div class="small text-uppercase text-muted">Turno</div><div class="fs-5 fw-semibold">{{ $m->turno }}</div></div>
        <div><div class="small text-uppercase text-muted">Grupo</div><div class="fs-5 fw-semibold"><span class="badge bg-secondary">G{{ $m->grupo }}</span></div></div>
        <div><div class="small text-uppercase text-muted">Status</div>
            @php
                $estiloStatus = match($m->status_id) {
                    3 => 'bg-success text-white',
                    2 => 'bg-warning text-dark',
                    default => 'bg-secondary text-white',
                };
            @endphp
            <div class="fs-5 fw-semibold"><span class="badge {{ $estiloStatus }}">{{ $estados[$m->status_id] ?? 'N/A' }}</span></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white fw-semibold">MP Nacional</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Peso batería</span><span class="fw-semibold">{{ fmtMvd($m->pesobateria) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tipo batería</span><span class="fw-semibold">{{ fmtMvd($m->bateriatipo) }}</span></li>
            </ul>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white fw-semibold">MP Importación</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Peso batería</span><span class="fw-semibold">{{ fmtMvd($m->pesobateriaimport) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tipo batería</span><span class="fw-semibold">{{ fmtMvd($m->bateriatipoimport) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Metálico</span><span class="fw-semibold">{{ fmtMvd($m->metalicoimport) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pasta</span><span class="fw-semibold">{{ fmtMvd($m->pastaimport) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Placas</span><span class="fw-semibold">{{ fmtMvd($m->placasimport) }}</span></li>
            </ul>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning fw-semibold">Insumos</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Carbonato sodio</span><span class="fw-semibold">{{ fmtMvd($m->carbonatoSodio) }}</span></li>
            </ul>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white fw-semibold">Salidas (Producción)</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Metálico</span><span class="fw-semibold">{{ fmtMvd($m->salidas_metalico) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Rejilla</span><span class="fw-semibold">{{ fmtMvd($m->salidas_rejilla) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Metálico fino</span><span class="fw-semibold">{{ fmtMvd($m->salidas_metalicofino) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pasta desulfurada</span><span class="fw-semibold">{{ fmtMvd($m->salidas_pastadesulfurada) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pasta sin desulfurar</span><span class="fw-semibold">{{ fmtMvd($m->salidas_pastasin) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Polipropileno (kg)</span><span class="fw-semibold">{{ fmtMvd($m->salidas_polipropilenokg) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">ABS (kg)</span><span class="fw-semibold">{{ fmtMvd($m->salidas_abskg) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Separador (kg)</span><span class="fw-semibold">{{ fmtMvd($m->salidas_separadorkg) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Descargas</span><span class="fw-semibold">{{ fmtMvd($m->salidas_descargas) }}</span></li>
            </ul>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white fw-semibold">Análisis Calidad</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Azúfer (%)</span><span class="fw-semibold">{{ fmtMvd($m->calidad_azufre) }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Humedad (%)</span><span class="fw-semibold">{{ fmtMvd($m->calidad_humedad) }}</span></li>
            </ul>
        </div>
    </div>
</div>
@endsection
