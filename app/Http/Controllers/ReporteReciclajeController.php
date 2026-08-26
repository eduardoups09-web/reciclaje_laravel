<?php

namespace App\Http\Controllers;

use App\Exports\ReciclajeExport;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $datos = DB::table('view_consultamovimientos')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha')
            ->orderByRaw("CASE WHEN turno = 'Diurno' THEN 0 ELSE 1 END")
            ->orderBy('grupo')
            ->get();

        $rendimiento = $this->calcularRendimiento($datos);

        $mesStr = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $filename = "Reporte_Reciclaje_{$mesStr}_{$anio}.pdf";

        $pdf = Pdf::loadView('reporte_reciclaje.pdf', compact('datos', 'mes', 'anio', 'rendimiento'))
            ->setPaper('letter', 'landscape');

        return $pdf->download($filename);
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
