@extends('layouts.app')
@section('titulo', 'Reportes Gerenciales - Roberto')

@section('contenido')
@php use App\Models\ReporteGerencial; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-graph-up text-success"></i> Reportes Gerenciales - Roberto</h3>
    <a href="{{ route('reportes-gerenciales.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('reportes-gerenciales.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                @for ($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}" @selected(($filtros['anio'] ?? '') == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('reportes-gerenciales.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mes</th>
                    <th class="text-end">Saldo Inicial</th>
                    <th class="text-end">Total Recepción</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Consumo</th>
                    <th class="text-end">Saldo Sin Descontar</th>
                    <th class="text-end">Maquila Enviada</th>
                    <th class="text-end">Maquila Recibida</th>
                    <th class="text-end">Saldo Descontado</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($registros as $r)
                <tr>
                    <td class="fw-semibold">{{ ReporteGerencial::MESES[$r->mes] ?? $r->mes }}</td>
                    <td class="text-end">{{ number_format($r->saldo_total, 2) }}</td>
                    <td class="text-end">{{ number_format($r->total_recepcion, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($r->subtotal, 2) }}</td>
                    <td class="text-end">{{ number_format($r->consumo, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($r->saldo_sin_descontar, 2) }}</td>
                    <td class="text-end">{{ number_format($r->maquila_enviada, 2) }}</td>
                    <td class="text-end">{{ number_format($r->maquila_recibida, 2) }}</td>
                    <td class="text-end fw-bold {{ $r->saldo_descontado < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($r->saldo_descontado, 2) }}
                    </td>
                    <td class="text-center">
                        @if ($r->guardado)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Guardado</span>
                        @else
                            <span class="badge bg-secondary"><i class="bi bi-calculator"></i> Calculado</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-info"
                                onclick="verPrevisualizacion({{ $r->mes }}, {{ $r->anio }}, '{{ ReporteGerencial::MESES[$r->mes] }}', {{ $r->saldo_total }}, {{ $r->total_recepcion }}, {{ $r->consumo }}, {{ $r->maquila_enviada }}, {{ $r->maquila_recibida }}, {{ $r->subtotal }}, {{ $r->saldo_sin_descontar }}, {{ $r->saldo_descontado }})"
                                title="Previsualizar">
                            <i class="bi bi-eye"></i>
                        </button>
                        @if ($r->guardado)
                            <a href="{{ route('reportes-gerenciales.edit', $r->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="{{ route('reportes-gerenciales.destroy', $r->id) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este reporte?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <a href="{{ route('reportes-gerenciales.create', ['mes' => $r->mes, 'anio' => $r->anio]) }}"
                               class="btn btn-sm btn-outline-success" title="Guardar este mes">
                                <i class="bi bi-save"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">Sin datos para el año seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal de Previsualización --}}
<div class="modal fade" id="modalPrevisualizacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-graph-up"></i> Reporte Gerencial - Roberto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h4 id="prev_titulo" class="fw-bold"></h4>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Saldo Inicial</span>
                            <span class="fw-bold fs-5" id="prev_saldo_total"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Total Recepción</span>
                            <span class="fw-bold fs-5" id="prev_total_recepcion"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom border-primary">
                            <span class="text-primary fw-semibold">Subtotal</span>
                            <span class="fw-bold fs-5 text-primary" id="prev_subtotal"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Consumo</span>
                            <span class="fw-bold fs-5" id="prev_consumo"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom border-warning">
                            <span class="text-warning fw-semibold">Saldo Sin Descontar</span>
                            <span class="fw-bold fs-5 text-warning" id="prev_saldo_sin_descontar"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Maquila Enviada</span>
                            <span class="fw-bold fs-5" id="prev_maquila_enviada"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Maquila Recibida</span>
                            <span class="fw-bold fs-5" id="prev_maquila_recibida"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom" id="prev_border_saldo_descontado">
                            <span class="fw-semibold" id="prev_label_saldo_descontado">Saldo Descontado</span>
                            <span class="fw-bold fs-5" id="prev_saldo_descontado"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2">Fórmulas:</h6>
                    <div class="small">
                        <div><strong>Subtotal</strong> = Saldo Inicial + Total Recepción = <span id="prev_formula_subtotal"></span></div>
                        <div><strong>Saldo Sin Descontar</strong> = Subtotal - Consumo = <span id="prev_formula_ssd"></span></div>
                        <div><strong>Saldo Descontado</strong> = Saldo Sin Descontar - Maquila Enviada - Maquila Recibida = <span id="prev_formula_sd"></span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formato(num) {
        return parseFloat(num).toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function verPrevisualizacion(mes, anio, nombreMes, saldoTotal, totalRecepcion, consumo, maquilaEnviada, maquilaRecibida, subtotal, saldoSinDescontar, saldoDescontado) {
        document.getElementById('prev_titulo').textContent = nombreMes + ' ' + anio;

        document.getElementById('prev_saldo_total').textContent = formato(saldoTotal);
        document.getElementById('prev_total_recepcion').textContent = formato(totalRecepcion);
        document.getElementById('prev_consumo').textContent = formato(consumo);
        document.getElementById('prev_maquila_enviada').textContent = formato(maquilaEnviada);
        document.getElementById('prev_maquila_recibida').textContent = formato(maquilaRecibida);

        document.getElementById('prev_subtotal').textContent = formato(subtotal);
        document.getElementById('prev_saldo_sin_descontar').textContent = formato(saldoSinDescontar);

        const el = document.getElementById('prev_saldo_descontado');
        el.textContent = formato(saldoDescontado);
        const border = document.getElementById('prev_border_saldo_descontado');
        const label = document.getElementById('prev_label_saldo_descontado');
        if (saldoDescontado < 0) {
            border.className = 'd-flex justify-content-between align-items-center p-2 border-bottom border-danger';
            el.className = 'fw-bold fs-5 text-danger';
            label.className = 'fw-semibold text-danger';
        } else {
            border.className = 'd-flex justify-content-between align-items-center p-2 border-bottom border-success';
            el.className = 'fw-bold fs-5 text-success';
            label.className = 'fw-semibold text-success';
        }

        document.getElementById('prev_formula_subtotal').textContent =
            formato(saldoTotal) + ' + ' + formato(totalRecepcion) + ' = ' + formato(subtotal);
        document.getElementById('prev_formula_ssd').textContent =
            formato(subtotal) + ' - ' + formato(consumo) + ' = ' + formato(saldoSinDescontar);
        document.getElementById('prev_formula_sd').textContent =
            formato(saldoSinDescontar) + ' - ' + formato(maquilaEnviada) + ' - ' + formato(maquilaRecibida) + ' = ' + formato(saldoDescontado);

        const modal = new bootstrap.Modal(document.getElementById('modalPrevisualizacion'));
        modal.show();
    }
</script>
@endpush
