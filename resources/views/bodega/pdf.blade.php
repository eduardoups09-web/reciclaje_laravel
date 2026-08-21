<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7.5pt; color: #000; }
        table { border-collapse: collapse; }

        .page-wrapper {
            padding: 1.5cm 2cm 2cm 2cm;
        }

        .header-table { width: 100%; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 4px; }
        .header-table td { vertical-align: top; padding: 0; }
        .header-left h1 { font-size: 18pt; margin-bottom: 2px; font-weight: bold; }
        .header-left p { font-size: 7pt; line-height: 1.3; }
        .header-right { text-align: right; }
        .header-right .contribuyente { font-size: 6pt; line-height: 1.2; text-align: right; }
        .header-right .titulo-orden { font-size: 8pt; font-weight: bold; margin-top: 4px; }
        .header-right .orden-num { font-size: 16pt; font-weight: bold; border: 2px solid #000; padding: 2px 18px; display: inline-block; margin-top: 3px; }

        .fechas-table { width: 100%; margin: 4px 0; font-size: 7.5pt; }
        .fechas-table td { padding: 1px 0; }

        .seccion { margin-top: 8px; margin-bottom: 3px; }
        .seccion-titulo { font-size: 10pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 1px; margin-bottom: 4px; }

        .campo { margin-bottom: 2px; font-size: 7.5pt; line-height: 1.4; }
        .campo-label { font-weight: bold; }
        .campo-valor { text-decoration: underline; }

        .bienes-boxes { width: 100%; margin: 4px 0; }
        .bienes-boxes td { border: 1px solid #000; padding: 3px 8px; font-size: 7pt; border-radius: 6px; }

        .tabla-cantidades { border-collapse: collapse; margin-top: 4px; font-size: 7pt; }
        .tabla-cantidades th, .tabla-cantidades td { border: 1px solid #000; padding: 1px 6px; text-align: center; }
        .tabla-cantidades th { background-color: #f0f0f0; font-weight: bold; }

        .total-box { border: 2px solid #000; padding: 4px 12px; font-size: 9pt; font-weight: bold; display: inline-block; border-radius: 4px; }

        .firmas-table { width: 100%; margin-top: 20px; }
        .firmas-table td { width: 33%; text-align: center; vertical-align: bottom; padding: 0 6px; }
        .firma-cuadro { border: 1px solid #000; height: 65px; border-radius: 6px; margin-bottom: 2px; }
        .firma-label { font-size: 7pt; font-weight: bold; border-top: 1px solid #000; padding-top: 2px; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td class="header-left" style="width: 70%;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="vertical-align: middle; padding-right: 8px;">
                                @php
                                    $logoPath = public_path('img/logo_fundametz.png');
                                    $logoData = base64_encode(file_get_contents($logoPath));
                                @endphp
                                <img src="data:image/png;base64,{{ $logoData }}" width="85" height="85" alt="Logo FUNDAMETZ">
                            </td>
                            <td style="vertical-align: middle;">
                                <h1>FUNDAMETZ S.A.</h1>
                                <p>
                                    R.U.C: 0992369825001 - AUT. S.R.I 1132576614<br>
                                    Planta 1:(Matriz) V&iacute;a a Daule-Km 30 . Telf. 5000204. Petrillo - Nobol<br>
                                    Planta 2: V&iacute;a a Daule - Km 16 . Telf. 5012994. Pascuales - Guayaquil<br>
                                    www.fundametz.com . Ecuador
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="header-right" style="width: 30%; text-align: right;">
                    <div class="contribuyente">
                        CONTRIBUYENTE ESPECIAL<br>
                        Y AGENTE DE RETENCI&Oacute;N<br>
                        RETENCI&Oacute;N #1125
                    </div>
                    <div class="titulo-orden">ORDEN DE DESPACHO</div><br>
                    <div class="orden-num">{{ $registros->first()->consecutivo ?? '-' }}</div>
                </td>
            </tr>
        </table>

        {{-- Fechas --}}
        <table class="fechas-table">
            <tr>
                <td><strong>FECHA DE INICIO DEL TRASLADO:</strong> <span class="campo-valor">{{ $registros->first()->fechainicio }}</span></td>
                <td><strong>FECHA DE EMISI&Oacute;N:</strong> <span class="campo-valor">{{ $registros->first()->fechaemision ?? '-' }}</span></td>
            </tr>
        </table>

        {{-- Motivo del traslado --}}
        <div class="seccion">
            <div class="seccion-titulo">MOTIVO DEL TRASLADO</div>
            <div class="campo"><span class="campo-valor">{{ $registros->first()->motivo ?? '-' }}</span></div>
        </div>

        {{-- Datos de emisi&oacute;n --}}
        <div class="seccion">
            <div class="campo"><span class="campo-label">FECHA DE EMISI&Oacute;N:</span> <span class="campo-valor">{{ $registros->first()->fechaemision ?? '-' }}</span></div>
            <div class="campo"><span class="campo-label">PUNTO DE PARTIDA:</span> <span class="campo-valor">{{ $registros->first()->partida ?? '-' }}</span></div>
        </div>

        {{-- Destinatario --}}
        <div class="seccion">
            <div class="seccion-titulo">DESTINATARIO</div>
            <div class="campo"><span class="campo-label">NOMBRE O RAZ&Oacute;N SOCIAL:</span> <span class="campo-valor">{{ $registros->first()->nombreDestinatario ?? '-' }}</span></div>
            <div class="campo"><span class="campo-label">RUC/CI:</span> <span class="campo-valor">{{ $registros->first()->rucDestinatario ?? '-' }}</span></div>
            <div class="campo"><span class="campo-label">PUNTO DE LLEGADA:</span> <span class="campo-valor">{{ $registros->first()->llegada ?? '-' }}</span></div>
        </div>

        {{-- Transportista --}}
        <div class="seccion">
            <div class="seccion-titulo">IDENTIFICACI&Oacute;N DEL TRANSPORTISTA</div>
            <div class="campo"><span class="campo-label">NOMBRE O RAZ&Oacute;N SOCIAL:</span> <span class="campo-valor">{{ $registros->first()->nombreTransportista ?? '-' }}</span></div>
            <div class="campo">                <span class="campo-label">RUC/CI:</span> <span class="campo-valor">{{ $registros->first()->rucTransportista ?? '-' }}</span></div>
            <div class="campo"><span class="campo-label">PLACA:</span> <span class="campo-valor">{{ $registros->first()->placaTransportista ?? '-' }}</span></div>
        </div>

        {{-- Bienes transportados --}}
        <div class="seccion">
            <div class="seccion-titulo">BIENES TRANSPORTADOS</div>

            <table class="bienes-boxes">
                <tr>
                    <td style="width: 30%;"><strong>UNIDAD:</strong> {{ $registros->first()->unidad ?? '-' }}</td>
                    <td style="width: 40%;"><strong>DESCRIPCI&Oacute;N:</strong> {{ $registros->first()->observacion ?? 'GU&Iacute;A DE REMISI&Oacute;N #' . $registros->first()->consecutivo }}</td>
                    <td style="width: 30%;"><strong>TIPO DE BATER&Iacute;A:</strong> {{ $registros->first()->tipobateria ?? '-' }}</td>
                </tr>
            </table>

            @php
                $all = $registros->toArray();
                $totalRegistros = count($all);
                $mitad = ceil($totalRegistros / 2);
            @endphp

            <table class="tabla-cantidades">
                <tr>
                    <th>Cantidad</th>
                    <th>Contenedor</th>
                    <th>Cantidad</th>
                    <th>Contenedor</th>
                </tr>
                @for ($i = 0; $i < $mitad; $i++)
                    <tr>
                        <td>{{ $all[$i]['cantidad'] ?? '' }}</td>
                        <td>{{ $all[$i]['contenedor'] ?? '' }}</td>
                        @if (isset($all[$i + $mitad]))
                            <td>{{ $all[$i + $mitad]['cantidad'] ?? '' }}</td>
                            <td>{{ $all[$i + $mitad]['contenedor'] ?? '' }}</td>
                        @else
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                @endfor
            </table>

            <div style="margin-top: 6px;">
                <div class="total-box">Total: {{ $total }}</div>
            </div>
        </div>

        {{-- Firmas --}}
        <table class="firmas-table">
            <tr>
                <td>
                    <div class="firma-cuadro"></div>
                    <div class="firma-label">DESPACHADO POR:</div>
                </td>
                <td>
                    <div class="firma-cuadro"></div>
                    <div class="firma-label">TRANSPORTADO POR:</div>
                </td>
                <td>
                    <div class="firma-cuadro"></div>
                    <div class="firma-label">RECIBIDO POR:</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
