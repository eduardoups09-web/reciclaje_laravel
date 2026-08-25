<?php

namespace App\Http\Controllers;

use App\Exports\ReciclajeExport;
use Illuminate\Http\Request;
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
}
