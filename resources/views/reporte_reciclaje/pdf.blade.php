<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: letter landscape; margin: 1cm 1cm 1cm 0.6cm; }
        * { padding: 0; box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 7pt; color: #000; }
        table { border-collapse: collapse; width: 100%; margin: 0 auto; }
        th, td { border: 1px solid #000; padding: 2px 3px; text-align: center; vertical-align: middle; white-space: nowrap; font-size: 7pt; }
        th { background-color: #D9E2F3; font-weight: bold; }

        .page { page-break-after: always; }
        .page:last-child { page-break-after: avoid; }

        .title-row { background-color: #2D5E8B; color: #fff; font-size: 10pt; font-weight: bold; font-style: italic; text-decoration: underline; text-align: center; padding: 4px 0; }
        .section-header { background-color: #8DB4E2; color: #fff; font-weight: bold; font-size: 7.5pt; text-align: center; }
        .col-header { background-color: #5B9BD5; color: #fff; font-weight: bold; font-size: 6.5pt; text-align: center; }

        .data-cell { text-align: center; }
        .data-left { text-align: left; }
        .number-cell { text-align: right; }

        .total-row { background-color: #D9E2F3; font-weight: bold; }
        .total-acum-row { background-color: #B4C6E7; font-weight: bold; }

        .page-num { text-align: right; font-size: 7pt; font-style: italic; margin-top: 2px; }

        .rend-table { margin-top: 6px; width: 100%; }
        .rend-table th { background-color: #5B9BD5; font-size: 7pt; }
        .rend-table td { font-size: 7pt; }
        .rend-label { text-align: left; font-weight: bold; background-color: #D9E2F3; }
    </style>
</head>
<body>
    @php
        $pageSize = 20;
        $pages = $datos->chunk($pageSize);
        $totalPages = $pages->count();
        $acumTotales = [
            'pesobateria' => 0, 'pesobateriaimport' => 0, 'metalicoimport' => 0,
            'pastaimport' => 0, 'placasimport' => 0, 'carbonatoSodio' => 0,
            'salidas_metalico' => 0, 'salidas_rejilla' => 0, 'salidas_metalicofino' => 0,
            'salidas_pastadesulfurada' => 0, 'salidas_descargas' => 0,
            'salidas_polipropilenokg' => 0, 'salidas_abskg' => 0,
            'suma_past_azufre' => 0, 'suma_past' => 0,
        ];
    @endphp

    @foreach($pages as $pageIdx => $pagina)
    <div class="page">
        {{-- Titulo --}}
        <div class="title-row">
            REPORTE RECICLAJE &nbsp;&nbsp; MES: {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}-{{ $anio }} &nbsp;&nbsp; HOJA# {{ $pageIdx + 1 }}/{{ $totalPages }}
        </div>

        <table>
            {{-- Headers seccion --}}
            <tr>
                <th colspan="3" class="section-header"></th>
                <th colspan="7" class="section-header">MATERIA PRIMA</th>
                <th colspan="1" class="section-header">INSUMOS</th>
                <th colspan="13" class="section-header">PRODUCCION</th>
                <th colspan="1" class="section-header">CC</th>
            </tr>
            {{-- Headers columnas --}}
            <tr>
                <th class="col-header">FECHA</th>
                <th class="col-header">TURNO</th>
                <th class="col-header">GRUPO</th>
                <th class="col-header">P.BATERI.</th>
                <th class="col-header">BATER.TIPO</th>
                <th class="col-header">P.BATERI.I</th>
                <th class="col-header">BATER.TIPO.I</th>
                <th class="col-header">METAL.I</th>
                <th class="col-header">PASTA.I</th>
                <th class="col-header">PLACAS.I</th>
                <th class="col-header">CARBO.S.</th>
                <th class="col-header">METALICO</th>
                <th class="col-header">%</th>
                <th class="col-header">REJILLA</th>
                <th class="col-header">%</th>
                <th class="col-header">META.FI</th>
                <th class="col-header">%</th>
                <th class="col-header">PASTA</th>
                <th class="col-header">%</th>
                <th class="col-header">DESC.</th>
                <th class="col-header">PP.</th>
                <th class="col-header">ABS</th>
                <th class="col-header">PLAST.</th>
                <th class="col-header">%CARB.</th>
                <th class="col-header">%S</th>
            </tr>

            @php
                $pagTotales = [
                    'pesobateria' => 0, 'pesobateriaimport' => 0, 'metalicoimport' => 0,
                    'pastaimport' => 0, 'placasimport' => 0, 'carbonatoSodio' => 0,
                    'salidas_metalico' => 0, 'salidas_rejilla' => 0, 'salidas_metalicofino' => 0,
                    'salidas_pastadesulfurada' => 0, 'salidas_descargas' => 0,
                    'salidas_polipropilenokg' => 0, 'salidas_abskg' => 0,
                    'suma_past_azufre' => 0, 'suma_past' => 0,
                ];
            @endphp

            @foreach($pagina as $d)
                @php
                    $r = (int) ($d->salidas_metalico ?? 0);
                    $v = (int) ($d->salidas_metalicofino ?? 0);
                    $x = (int) ($d->salidas_pastadesulfurada ?? 0);
                    $sumInput = (int) ($d->pesobateria ?? 0) + (int) ($d->pesobateriaimport ?? 0) + (int) ($d->metalicoimport ?? 0) + (int) ($d->pastaimport ?? 0) + (int) ($d->placasimport ?? 0);
                    $denomCarbonato = $r + $v + $x;

                    $pctMetalico = $sumInput == 0 ? 0 : round($r / $sumInput * 100, 1);
                    $pctRejilla = $sumInput == 0 ? 0 : round((int) ($d->salidas_rejilla ?? 0) / $sumInput * 100, 1);
                    $pctMetaFi = $sumInput == 0 ? 0 : round($v / $sumInput * 100, 1);
                    $pctPasta = $sumInput == 0 ? 0 : round($x / $sumInput * 100, 1);
                    $pctCarb = $denomCarbonato == 0 ? 0 : round((int) ($d->carbonatoSodio ?? 0) / $denomCarbonato * 100, 1);
                    $pctS = round((float) ($d->calidad_azufre ?? 0), 2);

                    $ab = (int) ($d->salidas_polipropilenokg ?? 0);
                    $ac = (int) ($d->salidas_abskg ?? 0);
                    $pctPlast = $sumInput == 0 ? 0 : round(($ab + $ac) / $sumInput * 100, 1);
                @endphp
                <tr>
                    <td class="data-left">{{ \Carbon\Carbon::parse($d->fecha)->format('m-d') }}</td>
                    <td class="data-cell">{{ $d->turno }}</td>
                    <td class="data-cell">{{ $d->grupo }}</td>
                    <td class="number-cell">{{ number_format((int)($d->pesobateria ?? 0), 0, ',', '.') }}</td>
                    <td class="data-cell">{{ $d->bateriatipo ?? '' }}</td>
                    <td class="number-cell">{{ number_format((int)($d->pesobateriaimport ?? 0), 0, ',', '.') }}</td>
                    <td class="data-cell">{{ $d->bateriatipoimport ?? '' }}</td>
                    <td class="number-cell">{{ number_format((int)($d->metalicoimport ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format((int)($d->pastaimport ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format((int)($d->placasimport ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format((int)($d->carbonatoSodio ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format($r, 0, ',', '.') }}</td>
                    <td class="number-cell">{{ $pctMetalico }}</td>
                    <td class="number-cell">{{ number_format((int)($d->salidas_rejilla ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ $pctRejilla }}</td>
                    <td class="number-cell">{{ number_format($v, 0, ',', '.') }}</td>
                    <td class="number-cell">{{ $pctMetaFi }}</td>
                    <td class="number-cell">{{ number_format($x, 0, ',', '.') }}</td>
                    <td class="number-cell">{{ $pctPasta }}</td>
                    <td class="number-cell">{{ number_format((int)($d->salidas_descargas ?? 0), 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format($ab, 0, ',', '.') }}</td>
                    <td class="number-cell">{{ number_format($ac, 0, ',', '.') }}</td>
                    <td class="number-cell">{{ $pctPlast }}</td>
                    <td class="number-cell">{{ number_format($pctCarb, 1) }}</td>
                    <td class="number-cell">{{ $pctS }}</td>
                </tr>
                @php
                    $pagTotales['pesobateria'] += (int) ($d->pesobateria ?? 0);
                    $pagTotales['pesobateriaimport'] += (int) ($d->pesobateriaimport ?? 0);
                    $pagTotales['metalicoimport'] += (int) ($d->metalicoimport ?? 0);
                    $pagTotales['pastaimport'] += (int) ($d->pastaimport ?? 0);
                    $pagTotales['placasimport'] += (int) ($d->placasimport ?? 0);
                    $pagTotales['carbonatoSodio'] += (int) ($d->carbonatoSodio ?? 0);
                    $pagTotales['salidas_metalico'] += $r;
                    $pagTotales['salidas_rejilla'] += (int) ($d->salidas_rejilla ?? 0);
                    $pagTotales['salidas_metalicofino'] += $v;
                    $pagTotales['salidas_pastadesulfurada'] += $x;
                    $pagTotales['salidas_descargas'] += (int) ($d->salidas_descargas ?? 0);
                    $pagTotales['salidas_polipropilenokg'] += $ab;
                    $pagTotales['salidas_abskg'] += $ac;
                    if ($pctS > 0 && $x > 0) {
                        $pagTotales['suma_past_azufre'] += $x * $pctS;
                        $pagTotales['suma_past'] += $x;
                    }
                @endphp
            @endforeach

            {{-- Total Pagina --}}
            @php
                $sumInputPag = $pagTotales['pesobateria'] + $pagTotales['pesobateriaimport'] + $pagTotales['metalicoimport'] + $pagTotales['pastaimport'] + $pagTotales['placasimport'];
                $denomCarbPag = $pagTotales['salidas_metalico'] + $pagTotales['salidas_metalicofino'] + $pagTotales['salidas_pastadesulfurada'];
                $pctMPag = $sumInputPag == 0 ? 0 : round($pagTotales['salidas_metalico'] / $sumInputPag * 100, 1);
                $pctRPag = $sumInputPag == 0 ? 0 : round($pagTotales['salidas_rejilla'] / $sumInputPag * 100, 1);
                $pctMFPag = $sumInputPag == 0 ? 0 : round($pagTotales['salidas_metalicofino'] / $sumInputPag * 100, 1);
                $pctPPag = $sumInputPag == 0 ? 0 : round($pagTotales['salidas_pastadesulfurada'] / $sumInputPag * 100, 1);
                $pctCarbPag = $denomCarbPag == 0 ? 0 : round($pagTotales['carbonatoSodio'] / $denomCarbPag * 100, 1);
                $pctPlastPag = $sumInputPag == 0 ? 0 : round(($pagTotales['salidas_polipropilenokg'] + $pagTotales['salidas_abskg']) / $sumInputPag * 100, 1);
                $pctSPag = $pagTotales['suma_past'] == 0 ? 0 : round($pagTotales['suma_past_azufre'] / $pagTotales['suma_past'], 2);
            @endphp
            <tr class="total-row">
                <td colspan="3">Total Pagina:</td>
                <td class="number-cell">{{ number_format($pagTotales['pesobateria'], 0, ',', '.') }}</td>
                <td></td>
                <td class="number-cell">{{ number_format($pagTotales['pesobateriaimport'], 0, ',', '.') }}</td>
                <td></td>
                <td class="number-cell">{{ number_format($pagTotales['metalicoimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['pastaimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['placasimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['carbonatoSodio'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_metalico'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctMPag }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_rejilla'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctRPag }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_metalicofino'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctMFPag }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_pastadesulfurada'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctPPag }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_descargas'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_polipropilenokg'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($pagTotales['salidas_abskg'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctPlastPag }}</td>
                <td class="number-cell">{{ number_format($pctCarbPag, 1) }}</td>
                <td class="number-cell">{{ $pctSPag }}</td>
            </tr>

            {{-- Total Acumulado --}}
            @php
                foreach($pagTotales as $k => $v) $acumTotales[$k] += $v;
                $sumInputAcum = $acumTotales['pesobateria'] + $acumTotales['pesobateriaimport'] + $acumTotales['metalicoimport'] + $acumTotales['pastaimport'] + $acumTotales['placasimport'];
                $denomCarbAcum = $acumTotales['salidas_metalico'] + $acumTotales['salidas_metalicofino'] + $acumTotales['salidas_pastadesulfurada'];
                $pctMAcum = $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_metalico'] / $sumInputAcum * 100, 1);
                $pctRAcum = $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_rejilla'] / $sumInputAcum * 100, 1);
                $pctMFAcum = $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_metalicofino'] / $sumInputAcum * 100, 1);
                $pctPAcum = $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_pastadesulfurada'] / $sumInputAcum * 100, 1);
                $pctCarbAcum = $denomCarbAcum == 0 ? 0 : round($acumTotales['carbonatoSodio'] / $denomCarbAcum * 100, 1);
                $pctPlastAcum = $sumInputAcum == 0 ? 0 : round(($acumTotales['salidas_polipropilenokg'] + $acumTotales['salidas_abskg']) / $sumInputAcum * 100, 1);
                $pctSAcum = $acumTotales['suma_past'] == 0 ? 0 : round($acumTotales['suma_past_azufre'] / $acumTotales['suma_past'], 2);
            @endphp
            <tr class="total-acum-row">
                <td colspan="3">Total Acumulado:</td>
                <td class="number-cell">{{ number_format($acumTotales['pesobateria'], 0, ',', '.') }}</td>
                <td></td>
                <td class="number-cell">{{ number_format($acumTotales['pesobateriaimport'], 0, ',', '.') }}</td>
                <td></td>
                <td class="number-cell">{{ number_format($acumTotales['metalicoimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['pastaimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['placasimport'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['carbonatoSodio'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_metalico'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctMAcum }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_rejilla'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctRAcum }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_metalicofino'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctMFAcum }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_pastadesulfurada'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctPAcum }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_descargas'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_polipropilenokg'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ number_format($acumTotales['salidas_abskg'], 0, ',', '.') }}</td>
                <td class="number-cell">{{ $pctPlastAcum }}</td>
                <td class="number-cell">{{ number_format($pctCarbAcum, 1) }}</td>
                <td class="number-cell">{{ $pctSAcum }}</td>
            </tr>
        </table>

        <div class="page-num">Pag {{ $pageIdx + 1 }}/{{ $totalPages }}</div>

        {{-- Rendimiento en la ultima pagina --}}
        @if($pageIdx == $totalPages - 1 && isset($rendimiento))
            <table class="rend-table">
                <tr>
                    <th style="width: 16.6%"></th>
                    <th style="width: 16.6%">TOTAL</th>
                    <th style="width: 16.6%">TOTAL BATERIAS</th>
                    <th style="width: 16.6%">TOTAL AUTOMOTRIZ</th>
                    <th style="width: 16.6%">TOTAL UPS</th>
                    <th style="width: 16.6%">TOTAL METALICOS</th>
                </tr>
                @foreach($rendimiento as $rend)
                    <tr>
                        <td class="rend-label">{{ $rend['label'] }}</td>
                        <td>{{ $rend['total'] }}%</td>
                        <td>{{ $rend['bateria'] }}%</td>
                        <td>{{ $rend['automotriz'] }}%</td>
                        <td>{{ $rend['ups'] }}%</td>
                        <td>{{ $rend['metalicos'] }}%</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
    @endforeach
</body>
</html>
