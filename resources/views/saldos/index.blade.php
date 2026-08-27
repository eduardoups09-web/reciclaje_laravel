@extends('layouts.app')
@section('titulo', 'Saldos')

@section('contenido')
@php
    use App\Models\Saldosinsert;
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-clipboard-data text-success"></i> Saldos de inventario</h3>
    <div>
        <a href="{{ route('saldos.create') }}" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</a>
    </div>
</div>

{{-- Filtros por Año y Mes --}}
<form method="get" action="{{ route('saldos.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                @foreach ($aniosDisponibles as $anio)
                    <option value="{{ $anio }}" @selected(($filtros['anio'] ?? '') == $anio)>{{ $anio }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select">
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}" @selected(($filtros['mes'] ?? '') == $num)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('saldos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">{{ $registros->count() }} registro(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th style="background-color: #1e293b; color: #fff;">Fecha</th>
                    <th style="background-color: #1e293b; color: #fff;">Turno</th>
                    <th style="background-color: #1e293b; color: #fff;">Grupo</th>
                    <th style="background-color: #166534; color: #fff;" class="text-end">Rec. Nac. Autom.</th>
                    <th style="background-color: #166534; color: #fff;" class="text-end">Rec. Nac. UPS</th>
                    <th style="background-color: #166534; color: #fff;" class="text-end">Rec. Imp. Autom.</th>
                    <th style="background-color: #166534; color: #fff;" class="text-end">Rec. Imp. UPS</th>
                    <th style="background-color: #166534; color: #fff;" class="text-end">Total Recepción</th>
                    <th style="background-color: #78350f; color: #fff;" class="text-end">Maquila Enviada</th>
                    <th style="background-color: #78350f; color: #fff;" class="text-end">Maquila Recibida</th>
                    <th style="background-color: #7f1d1d; color: #fff;" class="text-end">Consumo</th>
                    <th style="background-color: #155e75; color: #fff;" class="text-end">Automotriz</th>
                    <th style="background-color: #155e75; color: #fff;" class="text-end">UPS</th>
                    <th style="background-color: #155e75; color: #fff;" class="text-end">Saldo total</th>
                    <th style="background-color: #1e3a5f; color: #fff;" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if($cierreMesAnterior)
                <tr class="table-warning">
                    <td colspan="11"></td>
                    <td class="text-end {{ $cierreMesAnterior->saldototalinsertAutomotriz < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesAnterior->saldototalinsertAutomotriz) }}</td>
                    <td class="text-end {{ $cierreMesAnterior->saldototalinsertUPS < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesAnterior->saldototalinsertUPS) }}</td>
                    <td class="text-end {{ $cierreMesAnterior->saldototalinsert < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesAnterior->saldototalinsert) }}</td>
                    <td class="text-center text-muted small fw-bold">Cierre mes anterior</td>
                </tr>
                @endif
                @if($cierreMesActual)
                <tr class="table-warning">
                    <td colspan="3"></td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->rec_nac_auto ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->rec_nac_ups ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->rec_imp_auto ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->rec_imp_ups ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->total_recepcion ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->maquila_enviada ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->maquila_recibida ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($sumasMesActual->consumo ?? 0, 2) }}</td>
                    <td class="text-end {{ $cierreMesActual->saldototalinsertAutomotriz < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesActual->saldototalinsertAutomotriz) }}</td>
                    <td class="text-end {{ $cierreMesActual->saldototalinsertUPS < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesActual->saldototalinsertUPS) }}</td>
                    <td class="text-end {{ $cierreMesActual->saldototalinsert < 0 ? 'text-danger' : '' }}">{{ number_format($cierreMesActual->saldototalinsert) }}</td>
                    <td class="text-center text-muted small fw-bold">Cierre mes actual</td>
                </tr>
                @endif
            @forelse ($registros as $r)
                @php
                    // Valores diarios para back-calculation (iguales a los del controller)
                    $maquilaTotalDia = $r->daily_maquila_enviada + $r->daily_maquila_recibida;
                    $totalAutoDia = $r->daily_rec_nac_auto + $r->daily_rec_imp_auto;
                    $totalUpsDia = $r->daily_rec_nac_ups + $r->daily_rec_imp_ups;

                    // Día anterior usando consumo filtrado por tipo
                    if ($totalAutoDia == 0 && $maquilaTotalDia == 0) {
                        $diaAnteriorAuto = $r->saldototalinsertAutomotriz + $r->consumo_auto_calc;
                    } else {
                        $diaAnteriorAuto = $r->saldototalinsertAutomotriz - ($totalAutoDia - $maquilaTotalDia - $r->consumo_auto_calc);
                    }

                    // UPS = día anterior + total ups - consumo ups (maquila siempre 0)
                    $diaAnteriorUps = $r->saldototalinsertUPS - $totalUpsDia + $r->consumo_ups_calc;

                    if ($r->daily_total_recepcion == 0 && $maquilaTotalDia == 0) {
                        $diaAnteriorTotal = $r->saldototalinsert + $r->consumo_calc;
                    } else {
                        $diaAnteriorTotal = $r->saldototalinsert - ($r->daily_total_recepcion - $maquilaTotalDia - $r->consumo_calc);
                    }
                @endphp
                <tr>
                    <td>{{ $r->fechasaldoinsert }}</td>
                    <td>{{ $r->turnosaldoinsert }}</td>
                    <td>{{ $r->gruposaldoinsert }}</td>
                    <td class="text-end text-success">{{ number_format($r->rec_nac_auto, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($r->rec_nac_ups, 2) }}</td>
                    <td class="text-end text-info">{{ number_format($r->rec_imp_auto, 2) }}</td>
                    <td class="text-end text-info">{{ number_format($r->rec_imp_ups, 2) }}</td>
                    <td class="text-end">{{ number_format($r->total_recepcion_calc, 2) }}</td>
                    <td class="text-end">{{ number_format($r->maquila_enviada_calc, 2) }}</td>
                    <td class="text-end">{{ number_format($r->maquila_recibida_calc, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($r->consumo_calc, 2) }}</td>
                    <td class="text-end {{ $r->saldototalinsertAutomotriz < 0 ? 'text-danger' : '' }}">{{ number_format($r->saldototalinsertAutomotriz) }}</td>
                    <td class="text-end {{ $r->saldototalinsertUPS < 0 ? 'text-danger' : '' }}">{{ number_format($r->saldototalinsertUPS) }}</td>
                    <td class="text-end {{ $r->saldototalinsert < 0 ? 'text-danger' : '' }}">{{ number_format($r->saldototalinsert) }}</td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-info" title="Ver cálculo"
                                data-bs-toggle="modal" data-bs-target="#modalCalculo"
                                data-fecha="{{ $r->fechasaldoinsert }}"
                                data-turno="{{ $r->turnosaldoinsert }}"
                                data-rec-nac-auto="{{ $r->daily_rec_nac_auto }}"
                                data-rec-nac-ups="{{ $r->daily_rec_nac_ups }}"
                                data-rec-imp-auto="{{ $r->daily_rec_imp_auto }}"
                                data-rec-imp-ups="{{ $r->daily_rec_imp_ups }}"
                                data-total-recepcion="{{ $r->daily_total_recepcion }}"
                                data-consumo-auto="{{ $r->consumo_auto_calc }}"
                                data-consumo-ups="{{ $r->consumo_ups_calc }}"
                                data-consumo-total="{{ $r->consumo_calc }}"
                                data-maq-enviada="{{ $r->daily_maquila_enviada }}"
                                data-maq-recibida="{{ $r->daily_maquila_recibida }}"
                                data-maq-total="{{ $maquilaTotalDia }}"
                                data-total-auto="{{ $totalAutoDia }}"
                                data-total-ups="{{ $totalUpsDia }}"
                                data-dia-anterior-total="{{ $diaAnteriorTotal }}"
                                data-dia-anterior-auto="{{ $diaAnteriorAuto }}"
                                data-dia-anterior-ups="{{ $diaAnteriorUps }}"
                                data-saldo-auto="{{ $r->saldototalinsertAutomotriz }}"
                                data-saldo-ups="{{ $r->saldototalinsertUPS }}"
                                data-saldo-total="{{ $r->saldototalinsert }}">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('saldos.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="{{ route('saldos.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este saldo?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="15" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal de Cálculo --}}
<div class="modal fade" id="modalCalculo" tabindex="-1" aria-labelledby="modalCalculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCalculoLabel"><i class="bi bi-calculator"></i> Detalle de cálculo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Fecha:</strong> <span id="mc-fecha"></span></div>
                    <div class="col-md-6"><strong>Turno:</strong> <span id="mc-turno"></span></div>
                </div>
                <hr>

                <div class="row">
                    {{-- Columna: Recepciones --}}
                    <div class="col-md-6">
                        <h6 class="text-success"><i class="bi bi-box-arrow-in-down"></i> Recepciones</h6>
                        <table class="table table-sm table-bordered mb-3">
                            <tr><td>Rec. Nac. Autom.</td><td class="text-end" id="mc-rec-nac-auto"></td></tr>
                            <tr><td>Rec. Nac. UPS</td><td class="text-end" id="mc-rec-nac-ups"></td></tr>
                            <tr><td>Rec. Imp. Autom.</td><td class="text-end" id="mc-rec-imp-auto"></td></tr>
                            <tr><td>Rec. Imp. UPS</td><td class="text-end" id="mc-rec-imp-ups"></td></tr>
                            <tr class="table-light"><td><strong>Total Recepción</strong></td><td class="text-end" id="mc-total-recepcion"></td></tr>
                        </table>
                    </div>

                    {{-- Columna: Maquila --}}
                    <div class="col-md-6">
                        <h6 class="text-warning"><i class="bi bi-arrow-left-right"></i> Maquila</h6>
                        <table class="table table-sm table-bordered mb-3">
                            <tr><td>Maquila Enviada</td><td class="text-end" id="mc-maq-enviada"></td></tr>
                            <tr><td>Maquila Recibida</td><td class="text-end" id="mc-maq-recibida"></td></tr>
                            <tr class="table-light"><td><strong>Total Maquila</strong></td><td class="text-end" id="mc-maq-total"></td></tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    {{-- Columna: Consumo --}}
                    <div class="col-md-6">
                        <h6 class="text-danger"><i class="bi bi-fire"></i> Consumo</h6>
                        <table class="table table-sm table-bordered mb-3">
                            <tr><td>Consumo Automotriz</td><td class="text-end" id="mc-consumo-auto"></td></tr>
                            <tr><td>Consumo UPS</td><td class="text-end" id="mc-consumo-ups"></td></tr>
                            <tr class="table-light"><td><strong>Consumo Total</strong></td><td class="text-end" id="mc-consumo-total"></td></tr>
                        </table>
                    </div>

                    {{-- Columna: Totales por tipo --}}
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="bi bi-plus-circle"></i> Totales por tipo</h6>
                        <table class="table table-sm table-bordered mb-3">
                            <tr><td>Total Automotriz (Rec. Nac. + Rec. Imp.)</td><td class="text-end" id="mc-total-auto"></td></tr>
                            <tr><td>Total UPS (Rec. Nac. + Rec. Imp.)</td><td class="text-end" id="mc-total-ups"></td></tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h6 class="text-dark"><i class="bi bi-code-square"></i> Fórmulas aplicadas</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Concepto</th><th>Cálculo</th><th class="text-end">Resultado</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Saldo total</strong></td>
                            <td id="mc-formula-total" class="text-muted"></td>
                            <td class="text-end fw-bold" id="mc-saldo-total"></td>
                        </tr>
                        <tr>
                            <td><strong>Automotriz</strong></td>
                            <td id="mc-formula-auto" class="text-muted"></td>
                            <td class="text-end fw-bold" id="mc-saldo-auto"></td>
                        </tr>
                        <tr>
                            <td><strong>UPS</strong></td>
                            <td id="mc-formula-ups" class="text-muted"></td>
                            <td class="text-end fw-bold" id="mc-saldo-ups"></td>
                        </tr>
                    </tbody>
                </table>
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
document.getElementById('modalCalculo').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const fmt = n => parseFloat(n).toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('mc-fecha').textContent = btn.dataset.fecha;
    document.getElementById('mc-turno').textContent = btn.dataset.turno;

    document.getElementById('mc-rec-nac-auto').textContent = fmt(btn.dataset.recNacAuto);
    document.getElementById('mc-rec-nac-ups').textContent = fmt(btn.dataset.recNacUps);
    document.getElementById('mc-rec-imp-auto').textContent = fmt(btn.dataset.recImpAuto);
    document.getElementById('mc-rec-imp-ups').textContent = fmt(btn.dataset.recImpUps);
    document.getElementById('mc-total-recepcion').textContent = fmt(btn.dataset.totalRecepcion);

    document.getElementById('mc-maq-enviada').textContent = fmt(btn.dataset.maqEnviada);
    document.getElementById('mc-maq-recibida').textContent = fmt(btn.dataset.maqRecibida);
    document.getElementById('mc-maq-total').textContent = fmt(btn.dataset.maqTotal);

    document.getElementById('mc-consumo-auto').textContent = fmt(btn.dataset.consumoAuto);
    document.getElementById('mc-consumo-ups').textContent = fmt(btn.dataset.consumoUps);
    document.getElementById('mc-consumo-total').textContent = fmt(btn.dataset.consumoTotal);

    document.getElementById('mc-total-auto').textContent = fmt(btn.dataset.totalAuto);
    document.getElementById('mc-total-ups').textContent = fmt(btn.dataset.totalUps);

    const totalRecepcion = parseFloat(btn.dataset.totalRecepcion);
    const maqTotal = parseFloat(btn.dataset.maqTotal);
    const totalAuto = parseFloat(btn.dataset.totalAuto);
    const totalUps = parseFloat(btn.dataset.totalUps);
    const consumoAuto = parseFloat(btn.dataset.consumoAuto);
    const consumoUps = parseFloat(btn.dataset.consumoUps);
    const consumoTotal = parseFloat(btn.dataset.consumoTotal);
    const diaAntTotal = parseFloat(btn.dataset.diaAnteriorTotal);
    const diaAntAuto = parseFloat(btn.dataset.diaAnteriorAuto);
    const diaAntUps = parseFloat(btn.dataset.diaAnteriorUps);

    if (totalRecepcion == 0 && maqTotal == 0) {
        document.getElementById('mc-formula-total').textContent =
            fmt(diaAntTotal) + ' - ' + fmt(consumoTotal);
    } else {
        document.getElementById('mc-formula-total').textContent =
            fmt(diaAntTotal) + ' + (' + fmt(totalRecepcion) + ' - ' + fmt(maqTotal) + ') - ' + fmt(consumoTotal);
    }

    if (totalAuto == 0 && maqTotal == 0) {
        document.getElementById('mc-formula-auto').textContent =
            fmt(diaAntAuto) + ' - ' + fmt(consumoAuto);
    } else {
        document.getElementById('mc-formula-auto').textContent =
            fmt(diaAntAuto) + ' + (' + fmt(totalAuto) + ' - ' + fmt(maqTotal) + ') - ' + fmt(consumoAuto);
    }

    // UPS siempre: día anterior + total ups - consumo ups
    document.getElementById('mc-formula-ups').textContent =
        fmt(diaAntUps) + ' + ' + fmt(totalUps) + ' - ' + fmt(consumoUps);

    document.getElementById('mc-saldo-total').textContent = fmt(btn.dataset.saldoTotal);
    document.getElementById('mc-saldo-auto').textContent = fmt(btn.dataset.saldoAuto);
    document.getElementById('mc-saldo-ups').textContent = fmt(btn.dataset.saldoUps);
});
</script>
@endpush
