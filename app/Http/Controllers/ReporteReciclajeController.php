<?php

namespace App\Http\Controllers;

use App\Exports\ReciclajeExport;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReporteReciclajeController extends Controller
{
    public function index()
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $anioActual = (int) now()->format('Y');
        $anios = range($anioActual, $anioActual - 5);

        return view('reporte_reciclaje.index', compact('meses', 'anios'));
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'mes'   => ['required', 'integer', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            'anio'  => ['required', 'integer', 'min:2020', 'max:2030'],
        ]);

        $export = new ReciclajeExport($request->mes, $request->anio);
        $spreadsheet = $export->generate();

        $mesStr = str_pad($request->mes, 2, '0', STR_PAD_LEFT);
        $filename = "Reporte_Reciclaje_{$mesStr}_{$request->anio}.xlsx";

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempPath = storage_path("app/{$filename}");
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportarPdf(Request $request)
    {
        $request->validate([
            'mes'   => ['required', 'integer', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            'anio'  => ['required', 'integer', 'min:2020', 'max:2030'],
        ]);

        $mes = $request->mes;
        $anio = $request->anio;

        $mesStr = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $mesSiguiente = $mes + 1;
        $anioSiguiente = $anio;
        if ($mesSiguiente > 12) {
            $mesSiguiente = 1;
            $anioSiguiente++;
        }
        $mesStrSig = str_pad($mesSiguiente, 2, '0', STR_PAD_LEFT);

        $fechaInicio = "{$anio}-{$mesStr}-01";
        $fechaFin = "{$anioSiguiente}-{$mesStrSig}-01";

        $datos = collect(DB::select("
            SELECT
                md.fecha, md.grupo, md.turno, md.status_id, md.is_ajuste,
                mn.pesobateria, mn.bateriatipo,
                mi.pesobateriaimport, mi.bateriatipoimport, mi.metalicoimport, mi.pastaimport, mi.placasimport,
                i_carb.carbonatoSodio,
                s.metalico AS salidas_metalico, s.rejilla AS salidas_rejilla,
                s.metalicofino AS salidas_metalicofino, s.pastadesulfurada AS salidas_pastadesulfurada,
                s.descargas AS salidas_descargas,
                s.polipropilenokg AS salidas_polipropilenokg, s.abskg AS salidas_abskg,
                c.azufre AS calidad_azufre
            FROM movimientodetalle AS md
            LEFT JOIN (
                SELECT fechanacional, turnonacional, gruponacional,
                       ROUND(COALESCE(SUM(pesobateria), 0), 0) AS pesobateria,
                       TRIM(BOTH ',' FROM COALESCE(GROUP_CONCAT(DISTINCT REPLACE(bateriatipo, ',', '') SEPARATOR ','), '')) AS bateriatipo
                FROM mpnacional
                WHERE is_deleted = 0 AND is_ajuste = 0
                  AND fechanacional >= ? AND fechanacional < ?
                GROUP BY fechanacional, turnonacional, gruponacional
            ) mn ON mn.fechanacional = md.fecha AND mn.turnonacional = md.turno AND mn.gruponacional = md.grupo
            LEFT JOIN (
                SELECT fechaimport, turnoimport, grupoimport,
                       ROUND(COALESCE(SUM(pesobateriaimport), 0), 0) AS pesobateriaimport,
                       TRIM(BOTH ',' FROM COALESCE(GROUP_CONCAT(DISTINCT REPLACE(bateriatipoimport, ',', '') SEPARATOR ','), '')) AS bateriatipoimport,
                       ROUND(COALESCE(SUM(metalicoimport), 0), 0) AS metalicoimport,
                       ROUND(COALESCE(SUM(pastaimport), 0), 0) AS pastaimport,
                       ROUND(COALESCE(SUM(placasimport), 0), 0) AS placasimport
                FROM mpimport
                WHERE is_deleted = 0 AND is_ajuste = 0
                  AND fechaimport >= ? AND fechaimport < ?
                GROUP BY fechaimport, turnoimport, grupoimport
            ) mi ON mi.fechaimport = md.fecha AND mi.turnoimport = md.turno AND mi.grupoimport = md.grupo
            LEFT JOIN (
                SELECT fecha, turnoinsumo, grupoinsumo,
                       ROUND(COALESCE(SUM(carbonatoSodio), 0), 0) AS carbonatoSodio
                FROM insumos
                WHERE is_deleted = 0 AND is_ajuste = 0
                  AND fecha >= ? AND fecha < ?
                GROUP BY fecha, turnoinsumo, grupoinsumo
            ) i_carb ON i_carb.fecha = md.fecha AND i_carb.turnoinsumo = md.turno AND i_carb.grupoinsumo = md.grupo
            LEFT JOIN (
                SELECT fechasalida, turnosalida, gruposalida,
                       ROUND(COALESCE(SUM(metalico) * COALESCE(MAX(calculablemeta), 1), 0), 0) AS metalico,
                       ROUND(COALESCE(SUM(rejilla) * COALESCE(MAX(calculablereji), 1), 0), 0) AS rejilla,
                       ROUND(COALESCE(SUM(metalicofino) * COALESCE(MAX(calculablemetafino), 1), 0), 0) AS metalicofino,
                       ROUND(COALESCE(SUM(pastadesulfurada) * COALESCE(MAX(calculablepasta), 1), 0), 0) AS pastadesulfurada,
                       ROUND(COALESCE(SUM(polipropilenokg), 0), 0) AS polipropilenokg,
                       ROUND(COALESCE(SUM(abskg), 0), 0) AS abskg,
                       COALESCE(SUM(descargas), 0) AS descargas
                FROM salidas
                WHERE is_deleted = 0 AND is_ajuste = 0
                  AND fechasalida >= ? AND fechasalida < ?
                GROUP BY fechasalida, turnosalida, gruposalida
            ) s ON s.fechasalida = md.fecha AND s.turnosalida = md.turno AND s.gruposalida = md.grupo
            LEFT JOIN (
                SELECT fecha, turnocalidad,
                       AVG(azufre) AS azufre
                FROM analisiscalidad
                WHERE is_deleted = 0 AND is_ajuste = 0
                  AND fecha >= ? AND fecha < ?
                GROUP BY fecha, turnocalidad
            ) c ON c.fecha = md.fecha AND c.turnocalidad = md.turno
            WHERE md.is_deleted = 0 AND md.is_ajuste = 0
              AND md.fecha >= ? AND md.fecha < ?
            GROUP BY md.fecha, md.grupo, md.turno
            ORDER BY md.fecha, CASE WHEN md.turno = 'Diurno' THEN 0 ELSE 1 END, md.grupo
        ", [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin]));

        $rendimiento = $this->calcularRendimiento($datos);

        $filename = "Reporte_Reciclaje_{$mesStr}_{$anio}.pdf";
        $path = storage_path("app/{$filename}");

        $html = view('reporte_reciclaje.pdf', compact('datos', 'mes', 'anio', 'rendimiento'))->render();

        Browsershot::html($html)
            ->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe')
            ->format('Letter')
            ->landscape()
            ->margins(10, 10, 6, 10)
            ->showBackground()
            ->savePdf($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    protected function precalcularDatosPdf($datos): array
    {
        $pageSize = 20;
        $chunks = $datos->chunk($pageSize);
        $totalPages = $chunks->count();
        $acumTotales = [
            'pesobateria' => 0, 'pesobateriaimport' => 0, 'metalicoimport' => 0,
            'pastaimport' => 0, 'placasimport' => 0, 'carbonatoSodio' => 0,
            'salidas_metalico' => 0, 'salidas_rejilla' => 0, 'salidas_metalicofino' => 0,
            'salidas_pastadesulfurada' => 0, 'salidas_descargas' => 0,
            'salidas_polipropilenokg' => 0, 'salidas_abskg' => 0,
            'suma_past_azufre' => 0, 'suma_past' => 0,
        ];

        $paginas = [];

        foreach ($chunks as $pageIdx => $pagina) {
            $filas = [];
            $pagTotales = [
                'pesobateria' => 0, 'pesobateriaimport' => 0, 'metalicoimport' => 0,
                'pastaimport' => 0, 'placasimport' => 0, 'carbonatoSodio' => 0,
                'salidas_metalico' => 0, 'salidas_rejilla' => 0, 'salidas_metalicofino' => 0,
                'salidas_pastadesulfurada' => 0, 'salidas_descargas' => 0,
                'salidas_polipropilenokg' => 0, 'salidas_abskg' => 0,
                'suma_past_azufre' => 0, 'suma_past' => 0,
            ];

            foreach ($pagina as $d) {
                $r = (int) ($d->salidas_metalico ?? 0);
                $v = (int) ($d->salidas_metalicofino ?? 0);
                $x = (int) ($d->salidas_pastadesulfurada ?? 0);
                $rej = (int) ($d->salidas_rejilla ?? 0);
                $ab = (int) ($d->salidas_polipropilenokg ?? 0);
                $ac = (int) ($d->salidas_abskg ?? 0);
                $sumInput = (int) ($d->pesobateria ?? 0) + (int) ($d->pesobateriaimport ?? 0)
                    + (int) ($d->metalicoimport ?? 0) + (int) ($d->pastaimport ?? 0) + (int) ($d->placasimport ?? 0);
                $denomCarbonato = $r + $v + $x;
                $pctS = round((float) ($d->calidad_azufre ?? 0), 2);

                $filas[] = [
                    'fecha_fmt'    => \Carbon\Carbon::parse($d->fecha)->format('m-d'),
                    'turno'        => $d->turno,
                    'grupo'        => $d->grupo,
                    'pesobateria'  => number_format((int)($d->pesobateria ?? 0), 0, ',', '.'),
                    'bateriatipo'  => $d->bateriatipo ?? '',
                    'pesobateriaimport' => number_format((int)($d->pesobateriaimport ?? 0), 0, ',', '.'),
                    'bateriatipoimport' => $d->bateriatipoimport ?? '',
                    'metalicoimport'    => number_format((int)($d->metalicoimport ?? 0), 0, ',', '.'),
                    'pastaimport'       => number_format((int)($d->pastaimport ?? 0), 0, ',', '.'),
                    'placasimport'      => number_format((int)($d->placasimport ?? 0), 0, ',', '.'),
                    'carbonatoSodio'    => number_format((int)($d->carbonatoSodio ?? 0), 0, ',', '.'),
                    'salidas_metalico'  => number_format($r, 0, ',', '.'),
                    'pct_metalico'      => $sumInput == 0 ? 0 : round($r / $sumInput * 100, 1),
                    'salidas_rejilla'   => number_format($rej, 0, ',', '.'),
                    'pct_rejilla'       => $sumInput == 0 ? 0 : round($rej / $sumInput * 100, 1),
                    'salidas_metalicofino' => number_format($v, 0, ',', '.'),
                    'pct_metafi'        => $sumInput == 0 ? 0 : round($v / $sumInput * 100, 1),
                    'salidas_pastadesulfurada' => number_format($x, 0, ',', '.'),
                    'pct_pasta'         => $sumInput == 0 ? 0 : round($x / $sumInput * 100, 1),
                    'salidas_descargas' => number_format((int)($d->salidas_descargas ?? 0), 0, ',', '.'),
                    'salidas_polipropilenokg' => number_format($ab, 0, ',', '.'),
                    'salidas_abskg'     => number_format($ac, 0, ',', '.'),
                    'pct_plast'         => $sumInput == 0 ? 0 : round(($ab + $ac) / $sumInput * 100, 1),
                    'pct_carb'          => $denomCarbonato == 0 ? 0 : round((int)($d->carbonatoSodio ?? 0) / $denomCarbonato * 100, 1),
                    'pct_s'             => $pctS,
                ];

                $pagTotales['pesobateria'] += (int) ($d->pesobateria ?? 0);
                $pagTotales['pesobateriaimport'] += (int) ($d->pesobateriaimport ?? 0);
                $pagTotales['metalicoimport'] += (int) ($d->metalicoimport ?? 0);
                $pagTotales['pastaimport'] += (int) ($d->pastaimport ?? 0);
                $pagTotales['placasimport'] += (int) ($d->placasimport ?? 0);
                $pagTotales['carbonatoSodio'] += (int) ($d->carbonatoSodio ?? 0);
                $pagTotales['salidas_metalico'] += $r;
                $pagTotales['salidas_rejilla'] += $rej;
                $pagTotales['salidas_metalicofino'] += $v;
                $pagTotales['salidas_pastadesulfurada'] += $x;
                $pagTotales['salidas_descargas'] += (int) ($d->salidas_descargas ?? 0);
                $pagTotales['salidas_polipropilenokg'] += $ab;
                $pagTotales['salidas_abskg'] += $ac;
                if ($pctS > 0 && $x > 0) {
                    $pagTotales['suma_past_azufre'] += $x * $pctS;
                    $pagTotales['suma_past'] += $x;
                }
            }

            $sumInputPag = $pagTotales['pesobateria'] + $pagTotales['pesobateriaimport'] + $pagTotales['metalicoimport'] + $pagTotales['pastaimport'] + $pagTotales['placasimport'];
            $denomCarbPag = $pagTotales['salidas_metalico'] + $pagTotales['salidas_metalicofino'] + $pagTotales['salidas_pastadesulfurada'];

            foreach ($pagTotales as $k => $v) $acumTotales[$k] += $v;
            $sumInputAcum = $acumTotales['pesobateria'] + $acumTotales['pesobateriaimport'] + $acumTotales['metalicoimport'] + $acumTotales['pastaimport'] + $acumTotales['placasimport'];
            $denomCarbAcum = $acumTotales['salidas_metalico'] + $acumTotales['salidas_metalicofino'] + $acumTotales['salidas_pastadesulfurada'];

            $paginas[] = [
                'filas'      => $filas,
                'num'        => $pageIdx + 1,
                'is_last'    => $pageIdx == $totalPages - 1,
                'total_pag'  => [
                    'pesobateria'       => number_format($pagTotales['pesobateria'], 0, ',', '.'),
                    'pesobateriaimport' => number_format($pagTotales['pesobateriaimport'], 0, ',', '.'),
                    'metalicoimport'    => number_format($pagTotales['metalicoimport'], 0, ',', '.'),
                    'pastaimport'       => number_format($pagTotales['pastaimport'], 0, ',', '.'),
                    'placasimport'      => number_format($pagTotales['placasimport'], 0, ',', '.'),
                    'carbonatoSodio'    => number_format($pagTotales['carbonatoSodio'], 0, ',', '.'),
                    'salidas_metalico'  => number_format($pagTotales['salidas_metalico'], 0, ',', '.'),
                    'pct_metalico'      => $sumInputPag == 0 ? 0 : round($pagTotales['salidas_metalico'] / $sumInputPag * 100, 1),
                    'salidas_rejilla'   => number_format($pagTotales['salidas_rejilla'], 0, ',', '.'),
                    'pct_rejilla'       => $sumInputPag == 0 ? 0 : round($pagTotales['salidas_rejilla'] / $sumInputPag * 100, 1),
                    'salidas_metalicofino' => number_format($pagTotales['salidas_metalicofino'], 0, ',', '.'),
                    'pct_metafi'        => $sumInputPag == 0 ? 0 : round($pagTotales['salidas_metalicofino'] / $sumInputPag * 100, 1),
                    'salidas_pastadesulfurada' => number_format($pagTotales['salidas_pastadesulfurada'], 0, ',', '.'),
                    'pct_pasta'         => $sumInputPag == 0 ? 0 : round($pagTotales['salidas_pastadesulfurada'] / $sumInputPag * 100, 1),
                    'salidas_descargas' => number_format($pagTotales['salidas_descargas'], 0, ',', '.'),
                    'salidas_polipropilenokg' => number_format($pagTotales['salidas_polipropilenokg'], 0, ',', '.'),
                    'salidas_abskg'     => number_format($pagTotales['salidas_abskg'], 0, ',', '.'),
                    'pct_plast'         => $sumInputPag == 0 ? 0 : round(($pagTotales['salidas_polipropilenokg'] + $pagTotales['salidas_abskg']) / $sumInputPag * 100, 1),
                    'pct_carb'          => $denomCarbPag == 0 ? 0 : round($pagTotales['carbonatoSodio'] / $denomCarbPag * 100, 1),
                    'pct_s'             => $pagTotales['suma_past'] == 0 ? 0 : round($pagTotales['suma_past_azufre'] / $pagTotales['suma_past'], 2),
                ],
                'total_acum' => [
                    'pesobateria'       => number_format($acumTotales['pesobateria'], 0, ',', '.'),
                    'pesobateriaimport' => number_format($acumTotales['pesobateriaimport'], 0, ',', '.'),
                    'metalicoimport'    => number_format($acumTotales['metalicoimport'], 0, ',', '.'),
                    'pastaimport'       => number_format($acumTotales['pastaimport'], 0, ',', '.'),
                    'placasimport'      => number_format($acumTotales['placasimport'], 0, ',', '.'),
                    'carbonatoSodio'    => number_format($acumTotales['carbonatoSodio'], 0, ',', '.'),
                    'salidas_metalico'  => number_format($acumTotales['salidas_metalico'], 0, ',', '.'),
                    'pct_metalico'      => $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_metalico'] / $sumInputAcum * 100, 1),
                    'salidas_rejilla'   => number_format($acumTotales['salidas_rejilla'], 0, ',', '.'),
                    'pct_rejilla'       => $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_rejilla'] / $sumInputAcum * 100, 1),
                    'salidas_metalicofino' => number_format($acumTotales['salidas_metalicofino'], 0, ',', '.'),
                    'pct_metafi'        => $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_metalicofino'] / $sumInputAcum * 100, 1),
                    'salidas_pastadesulfurada' => number_format($acumTotales['salidas_pastadesulfurada'], 0, ',', '.'),
                    'pct_pasta'         => $sumInputAcum == 0 ? 0 : round($acumTotales['salidas_pastadesulfurada'] / $sumInputAcum * 100, 1),
                    'salidas_descargas' => number_format($acumTotales['salidas_descargas'], 0, ',', '.'),
                    'salidas_polipropilenokg' => number_format($acumTotales['salidas_polipropilenokg'], 0, ',', '.'),
                    'salidas_abskg'     => number_format($acumTotales['salidas_abskg'], 0, ',', '.'),
                    'pct_plast'         => $sumInputAcum == 0 ? 0 : round(($acumTotales['salidas_polipropilenokg'] + $acumTotales['salidas_abskg']) / $sumInputAcum * 100, 1),
                    'pct_carb'          => $denomCarbAcum == 0 ? 0 : round($acumTotales['carbonatoSodio'] / $denomCarbAcum * 100, 1),
                    'pct_s'             => $acumTotales['suma_past'] == 0 ? 0 : round($acumTotales['suma_past_azufre'] / $acumTotales['suma_past'], 2),
                ],
            ];
        }

        return $paginas;
    }

    protected function calcularRendimiento($datos): array
    {
        $condBateriaFn = fn($d) => ((int)($d->pesobateria ?? 0) + (int)($d->pesobateriaimport ?? 0)) > 0;
        $condAutoFn = fn($d) => ($d->bateriatipo === 'Automotriz') || ($d->bateriatipoimport === 'Automotriz');
        $condUpsFn = fn($d) => ($d->bateriatipo === 'UPS') || ($d->bateriatipoimport === 'UPS');
        $condMetalFn = fn($d) => ((int)($d->metalicoimport ?? 0) + (int)($d->pastaimport ?? 0) + (int)($d->placasimport ?? 0)) > 0;
        $condValidFn = fn($d) => (int)($d->salidas_pastadesulfurada ?? 0) > 0;

        $denomFn = fn($d) => (int)($d->pesobateria ?? 0) + (int)($d->pesobateriaimport ?? 0)
            + (int)($d->metalicoimport ?? 0) + (int)($d->pastaimport ?? 0) + (int)($d->placasimport ?? 0);

        $metas = [
            ['label' => '%REND.METALICO.GRUESO', 'col' => 'salidas_metalico'],
            ['label' => '%REND.REJILLA', 'col' => 'salidas_rejilla'],
            ['label' => '%REND.METALICO.FINO', 'col' => 'salidas_metalicofino'],
            ['label' => '%REND.PASTA.DESULFURADA', 'col' => 'salidas_pastadesulfurada'],
        ];

        $rendimiento = [];

        foreach ($metas as $m) {
            $col = $m['col'];
            $label = $m['label'];

            $numTotal = 0;
            $denTotal = 0;
            $numBateria = 0;
            $denBateria = 0;
            $numAuto = 0;
            $denAuto = 0;
            $numUps = 0;
            $denUps = 0;
            $numMetal = 0;
            $denMetal = 0;

            foreach ($datos as $d) {
                if (!$condValidFn($d)) continue;
                $val = (int)($d->$col ?? 0);
                $den = $denomFn($d);

                if ($den > 0) {
                    $numTotal += $val;
                    $denTotal += $den;
                }
                if ($condBateriaFn($d) && $den > 0 && $val > 0) {
                    $numBateria += $val;
                    $denBateria += $den;
                }
                if ($condAutoFn($d) && $den > 0 && $val > 0) {
                    $numAuto += $val;
                    $denAuto += $den;
                }
                if ($condUpsFn($d) && $den > 0 && $val > 0) {
                    $numUps += $val;
                    $denUps += $den;
                }
                if ($condMetalFn($d)) {
                    $metalDenom = (int)($d->metalicoimport ?? 0) + (int)($d->pastaimport ?? 0) + (int)($d->placasimport ?? 0);
                    $isNotZero = (int)($d->salidas_pastadesulfurada ?? 0) > 0;
                    if ($isNotZero && $val > 0 && $metalDenom > 0) {
                        $numMetal += $val;
                        $denMetal += $metalDenom;
                    }
                }
            }

            $rendimiento[] = [
                'label' => $label,
                'total' => $denTotal > 0 ? round($numTotal / $denTotal * 100, 2) : 0,
                'bateria' => $denBateria > 0 ? round($numBateria / $denBateria * 100, 2) : 0,
                'automotriz' => $denAuto > 0 ? round($numAuto / $denAuto * 100, 2) : 0,
                'ups' => $denUps > 0 ? round($numUps / $denUps * 100, 2) : 0,
                'metalicos' => $denMetal > 0 ? round($numMetal / $denMetal * 100, 2) : 0,
            ];
        }

        // %RENDIMIENTO (sum of all 4 metas)
        $numRT = 0; $denRT = 0;
        $numRB = 0; $denRB = 0;
        $numRA = 0; $denRA = 0;
        $numRU = 0; $denRU = 0;
        $numRM = 0; $denRM = 0;

        foreach ($datos as $d) {
            if (!$condValidFn($d)) continue;
            $den = $denomFn($d);
            $sum = (int)($d->salidas_metalico ?? 0) + (int)($d->salidas_rejilla ?? 0)
                + (int)($d->salidas_metalicofino ?? 0) + (int)($d->salidas_pastadesulfurada ?? 0);

            if ($den > 0) { $numRT += $sum; $denRT += $den; }
            if ($condBateriaFn($d) && $den > 0) { $numRB += $sum; $denRB += $den; }
            if ($condAutoFn($d) && $den > 0) { $numRA += $sum; $denRA += $den; }
            if ($condUpsFn($d) && $den > 0) { $numRU += $sum; $denRU += $den; }
            if ($condMetalFn($d)) {
                $metalDenom = (int)($d->metalicoimport ?? 0) + (int)($d->pastaimport ?? 0) + (int)($d->placasimport ?? 0);
                $isNotZero = (int)($d->salidas_pastadesulfurada ?? 0) > 0;
                if ($isNotZero && $metalDenom > 0) { $numRM += $sum; $denRM += $metalDenom; }
            }
        }

        $rendimiento[] = [
            'label' => '%RENDIMIENTO',
            'total' => $denRT > 0 ? round($numRT / $denRT * 100, 2) : 0,
            'bateria' => $denRB > 0 ? round($numRB / $denRB * 100, 2) : 0,
            'automotriz' => $denRA > 0 ? round($numRA / $denRA * 100, 2) : 0,
            'ups' => $denRU > 0 ? round($numRU / $denRU * 100, 2) : 0,
            'metalicos' => $denRM > 0 ? round($numRM / $denRM * 100, 2) : 0,
        ];

        // %PROMEDIO.AZUFRE
        $numAT = 0; $denAT = 0;
        $numAB = 0; $denAB = 0;
        $numAA = 0; $denAA = 0;
        $numAU = 0; $denAU = 0;
        $numAM = 0; $denAM = 0;

        foreach ($datos as $d) {
            $azufre = (float)($d->calidad_azufre ?? 0);
            if ($azufre <= 0) continue;

            $den = $denomFn($d);
            $pastVal = (int)($d->salidas_pastadesulfurada ?? 0);

            if ($pastVal > 0 && $den > 0) {
                $numAT += $pastVal * $azufre;
                $denAT += $pastVal;
            }
            if ($condBateriaFn($d) && $pastVal > 0 && $den > 0) {
                $numAB += $pastVal * $azufre;
                $denAB += $pastVal;
            }
            if ($condAutoFn($d) && $pastVal > 0 && $den > 0) {
                $numAA += $pastVal * $azufre;
                $denAA += $pastVal;
            }
            if ($condUpsFn($d) && $pastVal > 0 && $den > 0) {
                $numAU += $pastVal * $azufre;
                $denAU += $pastVal;
            }
            if ($condMetalFn($d) && $pastVal > 0) {
                $numAM += $pastVal * $azufre;
                $denAM += $pastVal;
            }
        }

        $rendimiento[] = [
            'label' => '%PROMEDIO.AZUFRE',
            'total' => $denAT > 0 ? round($numAT / $denAT, 2) : 0,
            'bateria' => $denAB > 0 ? round($numAB / $denAB, 2) : 0,
            'automotriz' => $denAA > 0 ? round($numAA / $denAA, 2) : 0,
            'ups' => $denAU > 0 ? round($numAU / $denAU, 2) : 0,
            'metalicos' => $denAM > 0 ? round($numAM / $denAM, 2) : 0,
        ];

        return $rendimiento;
    }
}
