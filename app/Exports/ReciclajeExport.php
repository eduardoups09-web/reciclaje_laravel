<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReciclajeExport
{
    protected int $mes;
    protected int $anio;

    public function __construct(int $mes, int $anio)
    {
        $this->mes = $mes;
        $this->anio = $anio;
    }

    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->writeHeaders($sheet);
        $this->writeData($sheet);
        $this->setColumnWidths($sheet);
        $sheet->setAutoFilter('A4:AG4');

        return $spreadsheet;
    }

    protected function setFill($sheet, string $range, string $argb): void
    {
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color($argb));
    }

    protected function writeHeaders($sheet): void
    {
        $mesStr = str_pad($this->mes, 2, '0', STR_PAD_LEFT);

        // Row 1: Title
        $sheet->mergeCells('A1:AG1');
        $sheet->setCellValue('A1', 'REPORTE DE RECICLAJE');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'underline' => 'single', 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $this->setFill($sheet, 'A1:AG1', 'FF2D5E8B');

        // Row 2: Subtitle
        $sheet->mergeCells('A2:AG2');
        $sheet->setCellValue('A2', "MES: {$mesStr} | ANO: {$this->anio}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'underline' => 'single', 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
        ]);
        $this->setFill($sheet, 'A2:AG2', 'FF4F81BD');

        // Row 3: Section headers
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Movimientos de Baterias (kg)');
        $sheet->mergeCells('I3:J3');
        $sheet->setCellValue('I3', 'Bateria Nacional (kg)');
        $sheet->mergeCells('K3:L3');
        $sheet->setCellValue('K3', 'Baterias Importadas (kg)');
        $sheet->mergeCells('M3:O3');
        $sheet->setCellValue('M3', 'Material Importado (Kg)');
        $sheet->mergeCells('P3:Q3');
        $sheet->setCellValue('P3', 'Insumos');
        $sheet->mergeCells('R3:AC3');
        $sheet->setCellValue('R3', 'Produccion (Kg)');
        $sheet->mergeCells('AI3:AV3');
        $sheet->setCellValue('AI3', 'ANALISIS DE % AZUFRE EN PASTA (%S)');

        foreach (['A3:H3', 'I3:J3', 'K3:L3', 'M3:O3', 'P3:Q3', 'R3:AC3'] as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
            ]);
            $this->setFill($sheet, $range, 'FF8DB4E2');
        }

        $sheet->getStyle('AI3:AV3')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $this->setFill($sheet, 'AI3:AV3', 'FFE0A96D');

        // Row 4: Column headers
        $headers = [
            'A4' => 'FECHA', 'B4' => 'GRUPO', 'C4' => 'TURNO', 'D4' => 'STATUS',
            'E4' => 'TOTAL RECEPCION', 'F4' => 'TOTAL DESPACHO', 'G4' => 'TOTAL CONSUMO', 'H4' => 'INVENTARIO',
            'I4' => 'CONSUMO BATERIA LOCAL', 'J4' => 'TIPO DE BATERIA',
            'K4' => 'CONSUMO BATERIA IMPORTADA', 'L4' => 'TIPO DE BATERIA IMPORTADA',
            'M4' => 'METALICO IMPORTADO', 'N4' => 'PASTA IMPORTADO', 'O4' => 'PLACAS IMPORTADAS',
            'P4' => 'CARBONATO DE SODIO', 'Q4' => '% CARBONATO SODIO',
            'R4' => 'PRODUCCION DE METALICO', 'S4' => '% METALICO',
            'T4' => 'REJILLA', 'U4' => '% REJILLA',
            'V4' => 'PRODUCCION DE METALICO FINO', 'W4' => '% METALICO FINO',
            'X4' => 'PRODUCCION DE PASTA DESULFURADA', 'Y4' => '% PASTA DESULFURADA',
            'Z4' => '# DESCARGAS PASTAS', 'AA4' => 'PRODUCCION DE PASTA SIN DESULFURAR',
            'AB4' => 'PRODUCCION DE POLIPROPILENO', 'AC4' => 'PRODUCCION DE ABS',
            'AD4' => '% PLASTICO', 'AE4' => 'PRODUCCION DE SEPARADOR',
            'AF4' => '% AZUFRE', 'AG4' => '% HUMEDAD',
            'AI4' => 'FECHA & TURNO', 'AJ4' => 'VARIABLE',
        ];

        for ($i = 1; $i <= 11; $i++) {
            $col = Coordinate::stringFromColumnIndex(37 + $i - 1);
            $headers[$col . '4'] = "MUESTRA {$i}";
        }
        $headers['AV4'] = 'VALORES PROMEDIO';

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Main headers A4:AG4
        $sheet->getStyle('A4:AG4')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ]);
        $this->setFill($sheet, 'A4:AG4', 'FF5B9BD5');

        // Analysis headers AI4:AV4
        $sheet->getStyle('AI4:AV4')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ]);
        $this->setFill($sheet, 'AI4:AV4', 'FF2C3E50');
    }

    protected function writeData($sheet): void
    {
        $datos = DB::table('view_consultamovimientos')
            ->whereMonth('fecha', $this->mes)
            ->whereYear('fecha', $this->anio)
            ->orderBy('fecha')
            ->orderByRaw("CASE WHEN turno = 'Diurno' THEN 0 ELSE 1 END")
            ->orderBy('grupo')
            ->get();

        $analisis = DB::table('analisiscalidad')
            ->whereMonth('fecha', $this->mes)
            ->whereYear('fecha', $this->anio)
            ->where('is_deleted', 0)
            ->orderBy('fecha')
            ->orderBy('turnocalidad')
            ->orderBy('hora')
            ->get()
            ->groupBy(function ($r) {
                return $r->fecha . '|' . $r->turnocalidad;
            });

        $saldos = DB::table('saldosinsert')
            ->whereMonth('fechasaldoinsert', $this->mes)
            ->whereYear('fechasaldoinsert', $this->anio)
            ->get()
            ->groupBy(function ($r) {
                return $r->fechasaldoinsert . '-' . $r->turnosaldoinsert;
            })
            ->map(fn($items) => $items->first());

        $row = 5;

        $intCols = ['E','F','G','H','I','K','M','N','O','P','R','T','V','X','Z','AA','AB','AC','AE'];

        foreach ($datos as $d) {
            $sheet->setCellValue("A{$row}", $d->fecha);
            $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $sheet->setCellValue("B{$row}", $d->grupo);
            $sheet->setCellValue("C{$row}", $d->turno);
            $sheet->setCellValue("D{$row}", $this->statusLabel($d->status_id));
            $sheet->setCellValue("E{$row}", (int) ($d->total_recepcion ?? 0));
            $sheet->setCellValue("F{$row}", (int) ($d->total_despacho ?? 0));
            $sheet->setCellValue("G{$row}", (int) ($d->total_consumo ?? 0));

            $saldoKey = $d->fecha . '-' . $d->turno;
            $saldo = $saldos->get($saldoKey);
            $sheet->setCellValue("H{$row}", (int) ($saldo->saldototalinsert ?? 0));

            $sheet->setCellValue("I{$row}", (int) ($d->pesobateria ?? 0));
            $sheet->setCellValue("J{$row}", $d->bateriatipo ?? '');
            $sheet->setCellValue("K{$row}", (int) ($d->pesobateriaimport ?? 0));
            $sheet->setCellValue("L{$row}", $d->bateriatipoimport ?? '');
            $sheet->setCellValue("M{$row}", (int) ($d->metalicoimport ?? 0));
            $sheet->setCellValue("N{$row}", (int) ($d->pastaimport ?? 0));
            $sheet->setCellValue("O{$row}", (int) ($d->placasimport ?? 0));
            $sheet->setCellValue("P{$row}", (int) ($d->carbonatoSodio ?? 0));

            $r = (int) ($d->salidas_metalico ?? 0);
            $v = (int) ($d->salidas_metalicofino ?? 0);
            $x = (int) ($d->salidas_pastadesulfurada ?? 0);
            $denomCarbonato = $r + $v + $x;
            $sheet->setCellValue("Q{$row}", $denomCarbonato == 0 ? 0 : round((int) ($d->carbonatoSodio ?? 0) / $denomCarbonato * 100, 4));

            $sheet->setCellValue("R{$row}", $r);
            $sumInput = (int) ($d->pesobateria ?? 0) + (int) ($d->pesobateriaimport ?? 0) + (int) ($d->metalicoimport ?? 0) + (int) ($d->pastaimport ?? 0) + (int) ($d->placasimport ?? 0);
            $sheet->setCellValue("S{$row}", $sumInput == 0 ? 0 : round($r / $sumInput * 100, 4));

            $t = (int) ($d->salidas_rejilla ?? 0);
            $sheet->setCellValue("T{$row}", $t);
            $sheet->setCellValue("U{$row}", $sumInput == 0 ? 0 : round($t / $sumInput * 100, 4));

            $sheet->setCellValue("V{$row}", $v);
            $sheet->setCellValue("W{$row}", $sumInput == 0 ? 0 : round($v / $sumInput * 100, 4));

            $sheet->setCellValue("X{$row}", $x);
            $sheet->setCellValue("Y{$row}", $sumInput == 0 ? 0 : round($x / $sumInput * 100, 4));

            $sheet->setCellValue("Z{$row}", (int) ($d->salidas_descargas ?? 0));
            $sheet->setCellValue("AA{$row}", (int) ($d->salidas_pastasin ?? 0));
            $sheet->setCellValue("AB{$row}", (int) ($d->salidas_polipropilenokg ?? 0));
            $sheet->setCellValue("AC{$row}", (int) ($d->salidas_abskg ?? 0));
            $ab = (int) ($d->salidas_polipropilenokg ?? 0);
            $ac = (int) ($d->salidas_abskg ?? 0);
            $sheet->setCellValue("AD{$row}", $sumInput == 0 ? 0 : round(($ab + $ac) / $sumInput * 100, 4));
            $sheet->setCellValue("AE{$row}", (int) ($d->salidas_separadorkg ?? 0));
            $sheet->setCellValue("AF{$row}", round((float) ($d->calidad_azufre ?? 0), 2));
            $sheet->setCellValue("AG{$row}", round((float) ($d->calidad_humedad ?? 0), 2));

            $sheet->getStyle("A{$row}:AG{$row}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
            ]);

            foreach ($intCols as $c) {
                $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle("S{$row}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("U{$row}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("W{$row}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("Y{$row}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("AD{$row}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("AF{$row}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("AG{$row}")->getNumberFormat()->setFormatCode('0.00');

            $row++;
        }

        $lastDataRow = $row - 1;

        $analysisRow = 5;
        foreach ($analisis as $turnoKey => $muestras) {
            $parts = explode('|', $turnoKey);
            $fecha = $parts[0];
            $turno = $parts[1];

            $phVals = $muestras->pluck('ph')->filter()->values();
            $tempVals = $muestras->pluck('temperatura')->filter()->values();
            $azufreVals = $muestras->pluck('azufre')->filter()->values();
            $horaVals = $muestras->pluck('hora')->filter()->values();

            $variables = [];
            if ($azufreVals->isNotEmpty()) $variables[] = ['name' => '%S', 'values' => $azufreVals];
            if ($phVals->isNotEmpty()) $variables[] = ['name' => 'PH', 'values' => $phVals];
            if ($tempVals->isNotEmpty()) $variables[] = ['name' => 'TEMP', 'values' => $tempVals];
            if ($horaVals->isNotEmpty()) $variables[] = ['name' => 'HORA', 'values' => $horaVals];

            $blockStart = $analysisRow;
            foreach ($variables as $idx => $var) {
                $sheet->setCellValue("AI{$analysisRow}", $idx === 0 ? $fecha . ' - ' . $turno : '');
                $sheet->setCellValue("AJ{$analysisRow}", $var['name']);

                $col = 37;
                foreach ($var['values']->slice(0, 11) as $val) {
                    $cellRef = Coordinate::stringFromColumnIndex($col) . $analysisRow;
                    $sheet->setCellValue($cellRef, $var['name'] === 'HORA' ? (string) $val : round((float) $val, 2));
                    $col++;
                }

                if ($var['name'] !== 'HORA') {
                    $sheet->setCellValue("AV{$analysisRow}", round($var['values']->avg(), 2));
                }

                $analysisRow++;
            }
            $blockEnd = $analysisRow - 1;

            for ($ar = $blockStart; $ar <= $blockEnd; $ar++) {
                $isTop = ($ar === $blockStart);
                $isBottom = ($ar === $blockEnd);

                $sheet->getStyle("AI{$ar}:AV{$ar}")->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
                    'borders' => [
                        'left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                        'right' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                        'top' => $isTop ? ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']] : ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
                        'bottom' => $isBottom ? ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']] : ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
                    ],
                ]);

                // AV column: blue fill, white font
                $sheet->getStyle("AV{$ar}")->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'borders' => [
                        'right' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                    ],
                ]);
                $this->setFill($sheet, "AV{$ar}", 'FF2980B9');

                // Conditional colors for sample values AK-AU
                for ($sc = 37; $sc <= 47; $sc++) {
                    $scRef = Coordinate::stringFromColumnIndex($sc) . $ar;
                    $val = $sheet->getCell($scRef)->getValue();
                    if ($val !== null && $val !== '' && $variables[$ar - $blockStart]['name'] !== 'HORA') {
                        $numVal = (float) $val;
                        if ($numVal <= 1.0) {
                            $sheet->getStyle($scRef)->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FFCCFFCC'));
                        } elseif ($numVal > 2.0) {
                            $sheet->getStyle($scRef)->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FFFFCCCC'));
                        } else {
                            $sheet->getStyle($scRef)->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FFFFFFCC'));
                        }
                    }
                }
            }
        }

        $allAzufre = collect();
        $allPh = collect();
        $allTemp = collect();
        foreach ($analisis as $muestras) {
            foreach ($muestras as $m) {
                if ($m->azufre !== null && $m->azufre != 0) $allAzufre[] = $m->azufre;
                if ($m->ph !== null && $m->ph != 0) $allPh[] = $m->ph;
                if ($m->temperatura !== null && $m->temperatura != 0) $allTemp[] = $m->temperatura;
            }
        }

        $azufreByCol = array_fill(0, 11, []);
        $phByCol = array_fill(0, 11, []);
        $tempByCol = array_fill(0, 11, []);
        foreach ($analisis as $muestras) {
            $azufreMuestras = $muestras->pluck('azufre')->filter()->values();
            $phMuestras = $muestras->pluck('ph')->filter()->values();
            $tempMuestras = $muestras->pluck('temperatura')->filter()->values();
            for ($i = 0; $i < 11; $i++) {
                if (isset($azufreMuestras[$i])) $azufreByCol[$i][] = (float) $azufreMuestras[$i];
                if (isset($phMuestras[$i])) $phByCol[$i][] = (float) $phMuestras[$i];
                if (isset($tempMuestras[$i])) $tempByCol[$i][] = (float) $tempMuestras[$i];
            }
        }

        $promedioVars = [
            ['name' => 'PROMEDIO MENSUAL %S', 'vals' => $allAzufre, 'byCol' => $azufreByCol],
            ['name' => 'PROMEDIO MENSUAL PH', 'vals' => $allPh, 'byCol' => $phByCol],
            ['name' => 'PROMEDIO MENSUAL TEMP', 'vals' => $allTemp, 'byCol' => $tempByCol],
        ];
        foreach ($promedioVars as $pv) {
            if ($pv['vals']->isEmpty()) continue;
            $sheet->setCellValue("AJ{$analysisRow}", $pv['name']);
            $col = 37;
            for ($i = 0; $i < 11; $i++) {
                $cellRef = Coordinate::stringFromColumnIndex($col) . $analysisRow;
                $avg = !empty($pv['byCol'][$i]) ? round(array_sum($pv['byCol'][$i]) / count($pv['byCol'][$i]), 2) : null;
                $sheet->setCellValue($cellRef, $avg);
                $col++;
            }
            if ($pv['name'] === 'PROMEDIO MENSUAL %S') {
                $sheet->setCellValue("AV{$analysisRow}",
                    "=IF(SUMIF(AD2:AD{$lastDataRow},\">0\",V2:V{$lastDataRow})<>0,ROUND(SUMPRODUCT(V2:V{$lastDataRow},AD2:AD{$lastDataRow})/SUMIF(AD2:AD{$lastDataRow},\">0\",V2:V{$lastDataRow}),2),0)");
            } else {
                $sheet->setCellValue("AV{$analysisRow}",
                    "=ROUND(AVERAGE(AK{$analysisRow}:AU{$analysisRow}),2)");
            }

            $sheet->getStyle("AJ{$analysisRow}:AV{$analysisRow}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
                'borders' => [
                    'left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                    'right' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                    'top' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                    'bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF888888']],
                ],
            ]);
            $this->setFill($sheet, "AJ{$analysisRow}:AV{$analysisRow}", 'FF2980B9');

            $analysisRow++;
        }

        if ($lastDataRow >= 5) {
            $rendimientoStartRow = $lastDataRow + 4;
            $this->writeRendimiento($sheet, $lastDataRow, $rendimientoStartRow);

            $totalsRow = $row;
            $sheet->mergeCells("A{$totalsRow}:D{$totalsRow}");
            $sheet->setCellValue("A{$totalsRow}", 'TOTALES');

            $sumCols = ['E', 'F', 'G', 'H', 'I', 'K', 'M', 'N', 'O', 'P', 'R', 'T', 'V', 'X', 'Z', 'AA', 'AB', 'AC', 'AE'];
            foreach ($sumCols as $col) {
                $sheet->setCellValue("{$col}{$totalsRow}", "=SUM({$col}5:{$col}{$lastDataRow})");
            }

            $LDR = $lastDataRow;
            $condValid = "(X5:X{$LDR}>0)*(X5:X{$LDR}<>\"\")";
            $denomRange = "(I5:I{$LDR}+K5:K{$LDR}+M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR})";

            $sheet->setCellValue("Q{$totalsRow}", "=IF((N(R{$totalsRow})+N(V{$totalsRow})+N(X{$totalsRow}))=0,0,N(P{$totalsRow})/(N(R{$totalsRow})+N(V{$totalsRow})+N(X{$totalsRow}))*100)");
            $sheet->setCellValue("S{$totalsRow}", "=IF(SUMPRODUCT({$condValid},{$denomRange})=0,0,ROUND(SUMPRODUCT({$condValid},R5:R{$LDR})/SUMPRODUCT({$condValid},{$denomRange})*100,2))");
            $sheet->setCellValue("U{$totalsRow}", "=IF(SUMPRODUCT({$condValid},{$denomRange})=0,0,ROUND(SUMPRODUCT({$condValid},T5:T{$LDR})/SUMPRODUCT({$condValid},{$denomRange})*100,2))");
            $sheet->setCellValue("W{$totalsRow}", "=IF(SUMPRODUCT({$condValid},{$denomRange})=0,0,ROUND(SUMPRODUCT({$condValid},V5:V{$LDR})/SUMPRODUCT({$condValid},{$denomRange})*100,2))");
            $sheet->setCellValue("Y{$totalsRow}", "=IF(SUMPRODUCT({$condValid},{$denomRange})=0,0,ROUND(SUMPRODUCT({$condValid},X5:X{$LDR})/SUMPRODUCT({$condValid},{$denomRange})*100,2))");
            $sheet->setCellValue("AD{$totalsRow}", "=IF(SUM(N(I{$totalsRow}),N(K{$totalsRow}),N(M{$totalsRow}),N(N{$totalsRow}),N(O{$totalsRow}))=0,0,(N(AB{$totalsRow})+N(AC{$totalsRow}))/SUM(N(I{$totalsRow}),N(K{$totalsRow}),N(M{$totalsRow}),N(N{$totalsRow}),N(O{$totalsRow}))*100)");
            $sheet->setCellValue("AF{$totalsRow}", "=IF(SUMIF(AF5:AF{$LDR},\">0\",X5:X{$LDR})<>0,ROUND(SUMPRODUCT(X5:X{$LDR},AF5:AF{$LDR})/SUMIF(AF5:AF{$LDR},\">0\",X5:X{$LDR}),2),0)");
            $sheet->setCellValue("AG{$totalsRow}", "=IF(SUMIF(AG5:AG{$LDR},\">0\",X5:X{$LDR})<>0,ROUND(SUMPRODUCT(X5:X{$LDR},AG5:AG{$LDR})/SUMIF(AG5:AG{$LDR},\">0\",X5:X{$LDR}),2),0)");

            $sheet->getStyle("A{$totalsRow}:AG{$totalsRow}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'bottom'],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
            ]);
            $this->setFill($sheet, "A{$totalsRow}:AG{$totalsRow}", 'FF203764');

            foreach ($intCols as $c) {
                $sheet->getStyle("{$c}{$totalsRow}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle("S{$totalsRow}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("U{$totalsRow}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("W{$totalsRow}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("Y{$totalsRow}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("AD{$totalsRow}")->getNumberFormat()->setFormatCode('0.0');
            $sheet->getStyle("Q{$totalsRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("AF{$totalsRow}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("AG{$totalsRow}")->getNumberFormat()->setFormatCode('0.00');
        }
    }

    protected function writeRendimiento($sheet, int $lastDataRow, int $startRow): void
    {
        $r = $startRow;
        $LDR = $lastDataRow;

        $LDR = $lastDataRow;

        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->setCellValue("D{$r}", 'TOTAL BATERIAS');
        $sheet->setCellValue("G{$r}", 'TOTAL AUTOMOTRIZ');
        $sheet->setCellValue("J{$r}", 'TOTAL UPS');
        $sheet->setCellValue("M{$r}", 'TOTAL METÁLICOS');
        $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ]);
        $this->setFill($sheet, "A{$r}:O{$r}", 'FFDCE6F1');
        $r++;

        $condBateria = "((I5:I{$LDR}>0)+(K5:K{$LDR}>0)>0)";
        $condAuto = "((J5:J{$LDR}=\"Automotriz\")+(L5:L{$LDR}=\"Automotriz\")>0)";
        $condUPS = "((J5:J{$LDR}=\"UPS\")+(L5:L{$LDR}=\"UPS\")>0)";
        $condMetal = "((M5:M{$LDR}>0)+(N5:N{$LDR}>0)+(O5:O{$LDR}>0)>0)";
        $condValid = "(X5:X{$LDR}>0)*(X5:X{$LDR}<>\"\")";
        $denom = "(I5:I{$LDR}+K5:K{$LDR}+M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR})";

        $metas = [
            ['label' => '%REND.METÁLICO.GRUESO', 'col' => 'R'],
            ['label' => '%REND.REJILLA', 'col' => 'T'],
            ['label' => '%REND.METÁLICO.FINO', 'col' => 'V'],
            ['label' => '%REND.PASTA.DESULFURADA', 'col' => 'X'],
        ];

        foreach ($metas as $m) {
            $col = $m['col'];
            $sheet->setCellValue("A{$r}", $m['label']);

            $sheet->setCellValue("C{$r}",
                "=IF(SUMPRODUCT(({$condValid})*{$denom})=0,0,ROUND(SUMPRODUCT(({$condValid})*{$col}5:{$col}{$LDR})/SUMPRODUCT(({$condValid})*{$denom})*100,2))");

            $sheet->setCellValue("D{$r}", "{$m['label']}.B");
            $sheet->setCellValue("F{$r}",
                "=IF(SUMPRODUCT({$condBateria}*{$condValid}*{$denom})=0,0,ROUND(SUMPRODUCT({$condBateria}*{$condValid}*({$col}5:{$col}{$LDR}>0)*{$col}5:{$col}{$LDR})/SUMPRODUCT({$condBateria}*{$condValid}*{$denom})*100,2))");

            $sheet->setCellValue("G{$r}", "{$m['label']}.A");
            $sheet->setCellValue("I{$r}",
                "=IF(SUMPRODUCT({$condAuto}*{$condValid}*{$denom})=0,0,ROUND(SUMPRODUCT({$condAuto}*{$condValid}*({$col}5:{$col}{$LDR}>0)*{$col}5:{$col}{$LDR})/SUMPRODUCT({$condAuto}*{$condValid}*{$denom})*100,2))");

            $sheet->setCellValue("J{$r}", "{$m['label']}.U");
            $sheet->setCellValue("L{$r}",
                "=IF(SUMPRODUCT({$condUPS}*{$condValid}*{$denom})=0,0,ROUND(SUMPRODUCT({$condUPS}*{$condValid}*({$col}5:{$col}{$LDR}>0)*{$col}5:{$col}{$LDR})/SUMPRODUCT({$condUPS}*{$condValid}*{$denom})*100,2))");

            $sheet->setCellValue("M{$r}", "{$m['label']}.M");
            $sheet->setCellValue("O{$r}",
                "=IF(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*({$col}5:{$col}{$LDR}>0)*(M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR}))=0,0,ROUND(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*({$col}5:{$col}{$LDR}>0)*{$col}5:{$col}{$LDR})/SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*({$col}5:{$col}{$LDR}>0)*(M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR}))*100,2))");

            $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
            ]);
            $this->setFill($sheet, "A{$r}:O{$r}", 'FFDCE6F1');
            $sheet->getStyle("C{$r}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle("O{$r}")->getNumberFormat()->setFormatCode('0.00');
            $r++;
        }

        $sheet->setCellValue("A{$r}", '%RENDIMIENTO');
        $sheet->setCellValue("C{$r}",
            "=IF(SUMPRODUCT(({$condValid})*{$denom})=0,0,ROUND((SUMPRODUCT(({$condValid})*R5:R{$LDR})+SUMPRODUCT(({$condValid})*T5:T{$LDR})+SUMPRODUCT(({$condValid})*V5:V{$LDR})+SUMPRODUCT(({$condValid})*X5:X{$LDR}))/(SUMPRODUCT(({$condValid})*{$denom}))*100,2))");
        $sheet->setCellValue("D{$r}", '%RENDIMIENTO.B');
        $sheet->setCellValue("F{$r}",
            "=IF(SUMPRODUCT({$condBateria}*{$condValid}*{$denom})=0,0,ROUND((SUMPRODUCT({$condBateria}*{$condValid}*(R5:R{$LDR}>0)*R5:R{$LDR})+SUMPRODUCT({$condBateria}*{$condValid}*(T5:T{$LDR}>0)*T5:T{$LDR})+SUMPRODUCT({$condBateria}*{$condValid}*(V5:V{$LDR}>0)*V5:V{$LDR})+SUMPRODUCT({$condBateria}*{$condValid}*(X5:X{$LDR}>0)*X5:X{$LDR}))/(SUMPRODUCT({$condBateria}*{$condValid}*{$denom}))*100,2))");
        $sheet->setCellValue("G{$r}", '%RENDIMIENTO.A');
        $sheet->setCellValue("I{$r}",
            "=IF(SUMPRODUCT({$condAuto}*{$condValid}*{$denom})=0,0,ROUND(SUMPRODUCT({$condAuto}*{$condValid}*((R5:R{$LDR}>0)*R5:R{$LDR}+(T5:T{$LDR}>0)*T5:T{$LDR}+(V5:V{$LDR}>0)*V5:V{$LDR}+(X5:X{$LDR}>0)*X5:X{$LDR}))/SUMPRODUCT({$condAuto}*{$condValid}*{$denom})*100,2))");
        $sheet->setCellValue("J{$r}", '%RENDIMIENTO.U');
        $sheet->setCellValue("L{$r}",
            "=IF(SUMPRODUCT({$condUPS}*{$condValid}*{$denom})=0,0,ROUND(SUMPRODUCT({$condUPS}*{$condValid}*((R5:R{$LDR}>0)*R5:R{$LDR}+(T5:T{$LDR}>0)*T5:T{$LDR}+(V5:V{$LDR}>0)*V5:V{$LDR}+(X5:X{$LDR}>0)*X5:X{$LDR}))/SUMPRODUCT({$condUPS}*{$condValid}*{$denom})*100,2))");
        $sheet->setCellValue("M{$r}", '%RENDIMIENTO.M');
        $sheet->setCellValue("O{$r}",
            "=IF(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR}))=0,0,ROUND(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(R5:R{$LDR}+T5:T{$LDR}+V5:V{$LDR}+X5:X{$LDR}))/SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(M5:M{$LDR}+N5:N{$LDR}+O5:O{$LDR}))*100,2))");
        $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ]);
        $this->setFill($sheet, "A{$r}:O{$r}", 'FFDCE6F1');
        $sheet->getStyle("C{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("O{$r}")->getNumberFormat()->setFormatCode('0.00');
        $r++;

        $sheet->setCellValue("A{$r}", '%PROMEDIO.AZUFRE');
        $sheet->setCellValue("C{$r}",
            "=IF(SUMIF(AF5:AF{$LDR},\">0\",X5:X{$LDR})=0,0,ROUND(SUMPRODUCT(X5:X{$LDR},AF5:AF{$LDR})/SUMIF(AF5:AF{$LDR},\">0\",X5:X{$LDR}),2))");
        $sheet->setCellValue("D{$r}", '%PROMEDIO.AZUFRE.B');
        $sheet->setCellValue("F{$r}",
            "=IF(SUMPRODUCT({$condBateria}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR})=0,0,ROUND(SUMPRODUCT({$condBateria}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}*AF5:AF{$LDR})/SUMPRODUCT({$condBateria}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}),2))");
        $sheet->setCellValue("G{$r}", '%PROMEDIO.AZUFRE.A');
        $sheet->setCellValue("I{$r}",
            "=IF(SUMPRODUCT({$condAuto}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR})=0,0,ROUND(SUMPRODUCT({$condAuto}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}*AF5:AF{$LDR})/SUMPRODUCT({$condAuto}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}),2))");
        $sheet->setCellValue("J{$r}", '%PROMEDIO.AZUFRE.U');
        $sheet->setCellValue("L{$r}",
            "=IF(SUMPRODUCT({$condUPS}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR})=0,0,ROUND(SUMPRODUCT({$condUPS}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}*AF5:AF{$LDR})/SUMPRODUCT({$condUPS}*{$condValid}*(AF5:AF{$LDR}>0)*X5:X{$LDR}),2))");
        $sheet->setCellValue("M{$r}", '%PROMEDIO.AZUFRE.M');
        $sheet->setCellValue("O{$r}",
            "=IF(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(AF5:AF{$LDR}>0)*X5:X{$LDR})=0,0,ROUND(SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(AF5:AF{$LDR}>0)*X5:X{$LDR}*AF5:AF{$LDR})/SUMPRODUCT(({$condMetal})*(X5:X{$LDR}<>\"\")*(X5:X{$LDR}<>0)*(AF5:AF{$LDR}>0)*X5:X{$LDR}),2))");
        $sheet->getStyle("A{$r}:O{$r}")->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
        ]);
        $this->setFill($sheet, "A{$r}:O{$r}", 'FFDCE6F1');
        $sheet->getStyle("C{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("I{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("O{$r}")->getNumberFormat()->setFormatCode('0.00');
    }

    protected function setColumnWidths($sheet): void
    {
        $widths = [
            'A' => 19.17, 'B' => 19.17, 'C' => 19.17, 'D' => 19.17,
            'E' => 19.17, 'F' => 19.17, 'G' => 19.17, 'H' => 19.17,
            'I' => 19.17, 'J' => 19.17, 'K' => 19.17, 'L' => 19.17,
            'M' => 19.17, 'N' => 19.17, 'O' => 19.17,
            'P' => 19.17, 'Q' => 19.17,
            'R' => 19.17, 'S' => 19.17, 'T' => 19.17, 'U' => 19.17,
            'V' => 19.17, 'W' => 19.17, 'X' => 19.17, 'Y' => 19.17,
            'Z' => 19.17, 'AA' => 19.17, 'AB' => 19.17, 'AC' => 19.17,
            'AD' => 19.17, 'AE' => 19.17, 'AF' => 19.17, 'AG' => 19.17,
            'AI' => 27.33, 'AJ' => 27.33,
        ];
        for ($i = 1; $i <= 11; $i++) {
            $col = Coordinate::stringFromColumnIndex(37 + $i - 1);
            $widths[$col] = ($i <= 9) ? 14.33 : 15.67;
        }
        $widths['AV'] = 23.5;

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    protected function statusLabel($statusId): string
    {
        return match ((int) $statusId) {
            4 => 'Aprobado',
            2 => 'Cerrado',
            default => 'Abierto',
        };
    }
}
