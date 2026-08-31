@extends('layouts.app')
@section('titulo', 'Reportes Gerenciales - Pablo')

@section('contenido')
@php use App\Models\ReporteGerencialPablo; @endphp

<style>
    .sec1, .sec2, .sec3 { display: none; }
    .sec1.active, .sec2.active, .sec3.active { display: table-cell; }
    .btn-toggle { font-size: 0.8rem; }

    #tablaPablo th, #tablaPablo td { border: 1px solid #adb5bd !important; }
    #tablaPablo .num { color: #000 !important; font-weight: 700 !important; }
    #tablaPablo .bg-sec1 { background-color: #d4edda !important; }
    #tablaPablo .bg-sec2 { background-color: #cce5ff !important; }
    #tablaPablo .bg-sec3 { background-color: #fff3cd !important; }
    #tablaPablo .bg-mes { background-color: #e2e3e5 !important; }
    #tablaPablo .bg-saldo { background-color: #f8f9fa !important; }
    #tablaPablo .bg-total { background-color: #ffeeba !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-graph-up text-success"></i> Reportes Gerenciales - Pablo</h3>
    <a href="{{ route('pablo.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
</div>

<form method="get" action="{{ route('pablo.index') }}" class="card card-body shadow-sm mb-3">
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
            <a href="{{ route('pablo.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="mb-2">
    <button onclick="toggleSeccion('sec1', this)" class="btn btn-sm btn-outline-success btn-toggle me-1">
        <i class="bi bi-eye"></i> Recepciones
    </button>
    <button onclick="toggleSeccion('sec2', this)" class="btn btn-sm btn-outline-primary btn-toggle me-1">
        <i class="bi bi-eye"></i> Consumo Baterías
    </button>
    <button onclick="toggleSeccion('sec3', this)" class="btn btn-sm btn-outline-warning btn-toggle">
        <i class="bi bi-eye"></i> Maquila
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table id="tablaPablo" class="table table-hover table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th class="bg-mes">Mes</th>
                    <th class="text-end sec1 bg-sec1">Rec. Nac. Autom.</th>
                    <th class="text-end sec1 bg-sec1">Rec. Nac. UPS</th>
                    <th class="text-end sec1 bg-sec1">Rec. Imp. Autom.</th>
                    <th class="text-end sec1 bg-sec1">Rec. Imp. UPS</th>
                    <th class="text-end bg-sec1">Total Recepción</th>
                    <th class="text-end sec2 bg-sec2">Nac. Automotriz</th>
                    <th class="text-end sec2 bg-sec2">Nac. UPS</th>
                    <th class="text-end sec2 bg-sec2">Imp. Automotriz</th>
                    <th class="text-end sec2 bg-sec2">Imp. UPS</th>
                    <th class="text-end bg-sec2">Consumo</th>
                    <th class="text-end sec3 bg-sec3">Maquila Enviada</th>
                    <th class="text-end sec3 bg-sec3">Maquila Recibida</th>
                    <th class="text-end bg-sec3">Total Maquila</th>
                    <th class="text-end bg-saldo">Saldo Inicial</th>
                    <th class="text-end bg-saldo">S. Cierre Autom.</th>
                    <th class="text-end bg-saldo">S. Cierre UPS</th>
                    <th class="text-end bg-saldo">Saldo Cierre</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @php
                $totales = [
                    'recepcion_nacional_automotriz' => 0, 'recepcion_nacional_ups' => 0,
                    'recepcion_importada_automotriz' => 0, 'recepcion_importada_ups' => 0,
                    'total_recepcion' => 0,
                    'bateria_nacional_automotriz' => 0, 'bateria_nacional_ups' => 0,
                    'bateria_importada_automotriz' => 0, 'bateria_importada_ups' => 0,
                    'consumo' => 0, 'maquila_enviada' => 0, 'maquila_recibida' => 0,
                    'total_maquila' => 0,
                    'saldo_total' => 0,
                    'saldo_cierre_automotriz' => 0, 'saldo_cierre_ups' => 0, 'saldo_cierre' => 0,
                ];
            @endphp
            @forelse ($registros as $r)
                @php
                    $totales['recepcion_nacional_automotriz']  += $r->recepcion_nacional_automotriz;
                    $totales['recepcion_nacional_ups']         += $r->recepcion_nacional_ups;
                    $totales['recepcion_importada_automotriz'] += $r->recepcion_importada_automotriz;
                    $totales['recepcion_importada_ups']        += $r->recepcion_importada_ups;
                    $totales['total_recepcion']                += $r->total_recepcion;
                    $totales['bateria_nacional_automotriz']    += $r->bateria_nacional_automotriz;
                    $totales['bateria_nacional_ups']           += $r->bateria_nacional_ups;
                    $totales['bateria_importada_automotriz']   += $r->bateria_importada_automotriz;
                    $totales['bateria_importada_ups']          += $r->bateria_importada_ups;
                    $totales['consumo']                        += $r->consumo;
                    $totales['maquila_enviada']                += $r->maquila_enviada;
                    $totales['maquila_recibida']               += $r->maquila_recibida;
                    $totales['total_maquila']                  += $r->total_maquila;
                    $totales['saldo_total']                    += $r->saldo_total;
                    $totales['saldo_cierre_automotriz']        += $r->saldo_cierre_automotriz;
                    $totales['saldo_cierre_ups']               += $r->saldo_cierre_ups;
                    $totales['saldo_cierre']                   += $r->saldo_cierre;
                @endphp
                <tr>
                    <td class="fw-semibold bg-mes">{{ ReporteGerencialPablo::MESES[$r->mes] ?? $r->mes }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($r->recepcion_nacional_automotriz, 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($r->recepcion_nacional_ups, 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($r->recepcion_importada_automotriz, 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($r->recepcion_importada_ups, 2) }}</td>
                    <td class="text-end bg-sec1 num">{{ number_format($r->total_recepcion, 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($r->bateria_nacional_automotriz, 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($r->bateria_nacional_ups, 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($r->bateria_importada_automotriz, 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($r->bateria_importada_ups, 2) }}</td>
                    <td class="text-end bg-sec2 num">{{ number_format($r->consumo, 2) }}</td>
                    <td class="text-end sec3 bg-sec3 num">{{ number_format($r->maquila_enviada, 2) }}</td>
                    <td class="text-end sec3 bg-sec3 num">{{ number_format($r->maquila_recibida, 2) }}</td>
                    <td class="text-end bg-sec3 num">{{ number_format($r->total_maquila, 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($r->saldo_total, 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($r->saldo_cierre_automotriz, 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($r->saldo_cierre_ups, 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($r->saldo_cierre, 2) }}</td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-info"
                                onclick="verPrevisualizacion({{ json_encode([
                                    $r->mes, $r->anio, ReporteGerencialPablo::MESES[$r->mes],
                                    $r->saldo_total, $r->total_recepcion,
                                    $r->recepcion_nacional_automotriz, $r->recepcion_nacional_ups,
                                    $r->recepcion_importada_automotriz, $r->recepcion_importada_ups,
                                    $r->bateria_nacional_automotriz, $r->bateria_nacional_ups,
                                    $r->bateria_importada_automotriz, $r->bateria_importada_ups,
                                    $r->consumo, $r->maquila_enviada, $r->maquila_recibida, $r->total_maquila,
                                    $r->saldo_cierre,
                                    $r->saldo_cierre_automotriz, $r->saldo_cierre_ups,
                                ]) }})"
                                title="Previsualizar">
                            <i class="bi bi-eye"></i>
                        </button>
                        @if ($r->guardado)
                            <a href="{{ route('pablo.edit', $r->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="{{ route('pablo.destroy', $r->id) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este reporte?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @else
                            <a href="{{ route('pablo.create', ['mes' => $r->mes, 'anio' => $r->anio]) }}"
                               class="btn btn-sm btn-outline-success" title="Guardar este mes">
                                <i class="bi bi-save"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="19" class="text-center text-muted py-4">Sin datos para el año seleccionado.</td></tr>
            @endforelse
            </tbody>
            @if ($registros->isNotEmpty())
            <tfoot class="fw-bold">
                <tr>
                    <td class="bg-mes">TOTALES</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($totales['recepcion_nacional_automotriz'], 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($totales['recepcion_nacional_ups'], 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($totales['recepcion_importada_automotriz'], 2) }}</td>
                    <td class="text-end sec1 bg-sec1 num">{{ number_format($totales['recepcion_importada_ups'], 2) }}</td>
                    <td class="text-end bg-sec1 num">{{ number_format($totales['total_recepcion'], 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($totales['bateria_nacional_automotriz'], 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($totales['bateria_nacional_ups'], 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($totales['bateria_importada_automotriz'], 2) }}</td>
                    <td class="text-end sec2 bg-sec2 num">{{ number_format($totales['bateria_importada_ups'], 2) }}</td>
                    <td class="text-end bg-sec2 num">{{ number_format($totales['consumo'], 2) }}</td>
                    <td class="text-end sec3 bg-sec3 num">{{ number_format($totales['maquila_enviada'], 2) }}</td>
                    <td class="text-end sec3 bg-sec3 num">{{ number_format($totales['maquila_recibida'], 2) }}</td>
                    <td class="text-end bg-sec3 num">{{ number_format($totales['total_maquila'], 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($totales['saldo_total'], 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($totales['saldo_cierre_automotriz'], 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($totales['saldo_cierre_ups'], 2) }}</td>
                    <td class="text-end bg-saldo num">{{ number_format($totales['saldo_cierre'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Modal de Previsualización --}}
<div class="modal fade" id="modalPrevisualizacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-graph-up"></i> Reporte Gerencial - Pablo</h5>
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
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-success">Recepción Nacional Automotriz</span>
                            <span class="fw-bold fs-5 text-success" id="prev_rn_auto"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-success">Recepción Nacional UPS</span>
                            <span class="fw-bold fs-5 text-success" id="prev_rn_ups"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-info">Recepción Importada Automotriz</span>
                            <span class="fw-bold fs-5 text-info" id="prev_ri_auto"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-info">Recepción Importada UPS</span>
                            <span class="fw-bold fs-5 text-info" id="prev_ri_ups"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-primary">Batería Nacional Automotriz</span>
                            <span class="fw-bold fs-5 text-primary" id="prev_bn_auto"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-primary">Batería Nacional UPS</span>
                            <span class="fw-bold fs-5 text-primary" id="prev_bn_ups"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-warning">Batería Importada Automotriz</span>
                            <span class="fw-bold fs-5 text-warning" id="prev_bi_auto"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-warning">Batería Importada UPS</span>
                            <span class="fw-bold fs-5 text-warning" id="prev_bi_ups"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-muted">Consumo</span>
                            <span class="fw-bold fs-5" id="prev_consumo"></span>
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
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <span class="text-dark fw-semibold">Total Maquila</span>
                            <span class="fw-bold fs-5" id="prev_total_maquila"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom" id="prev_border_saldo_cierre">
                            <span class="fw-semibold" id="prev_label_saldo_cierre">Saldo Cierre</span>
                            <span class="fw-bold fs-5" id="prev_saldo_cierre"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom" id="prev_border_sc_auto">
                            <span class="text-dark fw-semibold" id="prev_label_sc_auto">Saldo Cierre Automotriz</span>
                            <span class="fw-bold fs-5" id="prev_sc_auto"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom" id="prev_border_sc_ups">
                            <span class="text-dark fw-semibold" id="prev_label_sc_ups">Saldo Cierre UPS</span>
                            <span class="fw-bold fs-5" id="prev_sc_ups"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2">Fórmulas:</h6>
                    <div class="small">
                        <div><strong>Total Maquila</strong> = Maquila Enviada + Maquila Recibida</div>
                        <div><strong>Saldo Cierre</strong> = Saldo Inicial + Total Recepción - Consumo - Total Maquila</div>
                        <div><strong>Saldo Cierre Automotriz</strong> = saldos.cantidadAutomotriz (día 01 del mes)</div>
                        <div><strong>Saldo Cierre UPS</strong> = saldos.cantidadUPS (día 01 del mes)</div>
                        <div><strong>Consumo</strong> = Nac. Automotriz + Nac. UPS + Imp. Automotriz + Imp. UPS</div>
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
    function toggleSeccion(seccion, btn) {
        const cols = document.querySelectorAll('.' + seccion);
        const isActive = cols.length > 0 && cols[0].classList.contains('active');
        cols.forEach(col => col.classList.toggle('active'));

        const icon = btn.querySelector('i');
        if (isActive) {
            icon.className = 'bi bi-eye';
        } else {
            icon.className = 'bi bi-eye-slash';
        }
    }

    function formato(num) {
        return parseFloat(num).toLocaleString('es-EC', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function estiloValor(el, border, label, valor) {
        if (valor < 0) {
            el.className = 'fw-bold fs-5 text-danger';
            if (border) border.className = 'd-flex justify-content-between align-items-center p-2 border-bottom border-danger';
            if (label) label.className = 'text-dark fw-semibold text-danger';
        } else {
            el.className = 'fw-bold fs-5 text-success';
            if (border) border.className = 'd-flex justify-content-between align-items-center p-2 border-bottom border-success';
            if (label) label.className = 'text-dark fw-semibold text-success';
        }
    }

    function verPrevisualizacion(data) {
        const [mes, anio, nombreMes, saldoTotal, totalRecepcion,
               rnAuto, rnUps, riAuto, riUps,
               bnAuto, bnUps, biAuto, biUps,
               consumo, maquilaEnviada, maquilaRecibida, totalMaquila, saldoCierre,
               scAuto, scUps] = data;

        document.getElementById('prev_titulo').textContent = nombreMes + ' ' + anio;
        document.getElementById('prev_saldo_total').textContent = formato(saldoTotal);
        document.getElementById('prev_total_recepcion').textContent = formato(totalRecepcion);
        document.getElementById('prev_rn_auto').textContent = formato(rnAuto);
        document.getElementById('prev_rn_ups').textContent = formato(rnUps);
        document.getElementById('prev_ri_auto').textContent = formato(riAuto);
        document.getElementById('prev_ri_ups').textContent = formato(riUps);
        document.getElementById('prev_bn_auto').textContent = formato(bnAuto);
        document.getElementById('prev_bn_ups').textContent = formato(bnUps);
        document.getElementById('prev_bi_auto').textContent = formato(biAuto);
        document.getElementById('prev_bi_ups').textContent = formato(biUps);
        document.getElementById('prev_consumo').textContent = formato(consumo);
        document.getElementById('prev_maquila_enviada').textContent = formato(maquilaEnviada);
        document.getElementById('prev_maquila_recibida').textContent = formato(maquilaRecibida);
        document.getElementById('prev_total_maquila').textContent = formato(totalMaquila);

        const elSc = document.getElementById('prev_saldo_cierre');
        elSc.textContent = formato(saldoCierre);
        estiloValor(elSc, document.getElementById('prev_border_saldo_cierre'), document.getElementById('prev_label_saldo_cierre'), saldoCierre);

        const elScAuto = document.getElementById('prev_sc_auto');
        elScAuto.textContent = formato(scAuto);
        estiloValor(elScAuto, document.getElementById('prev_border_sc_auto'), document.getElementById('prev_label_sc_auto'), scAuto);

        const elScUps = document.getElementById('prev_sc_ups');
        elScUps.textContent = formato(scUps);
        estiloValor(elScUps, document.getElementById('prev_border_sc_ups'), document.getElementById('prev_label_sc_ups'), scUps);

        const modal = new bootstrap.Modal(document.getElementById('modalPrevisualizacion'));
        modal.show();
    }
</script>
@endpush
