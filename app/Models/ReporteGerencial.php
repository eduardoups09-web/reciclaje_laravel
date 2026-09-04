<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteGerencial extends Model
{
    protected $table = 'reporte_gerencial';

    protected $fillable = [
        'mes', 'anio',
        'saldo_total', 'total_recepcion', 'consumo',
        'maquila_enviada', 'maquila_recibida',
    ];

    public const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, ?int $mes, ?int $anio)
    {
        if ($mes) {
            $query->where('mes', $mes);
        }
        if ($anio) {
            $query->where('anio', $anio);
        }
        return $query;
    }

    public static function calcularValores(int $mes, int $anio): array
    {
        // 1. Saldo total: siempre leer de tabla saldos del día 01 de cada mes
        $fechaPrimeroMes = Carbon::create($anio, $mes, 1)->toDateString();
        $saldo = Saldo::where('is_deleted', 0)
            ->where('fechasaldo', $fechaPrimeroMes)
            ->first();
        $saldo_total = $saldo->cantidadsaldo ?? 0;

        // 2. Total recepción: suma Cantidad de ingresosInventarios del mes
        $total_recepcion = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->sum('Cantidad');

        // 3. Consumo: suma mpnacional + mpimport del mes
        $consumoNacional = DB::table('mpnacional')
            ->where('is_deleted', 0)
            ->whereMonth('fechanacional', $mes)
            ->whereYear('fechanacional', $anio)
            ->sum('pesobateria');
        $consumoImport = DB::table('mpimport')
            ->where('is_deleted', 0)
            ->whereMonth('fechaimport', $mes)
            ->whereYear('fechaimport', $anio)
            ->sum('pesobateriaimport');
        $consumo = $consumoNacional + $consumoImport;

        // 4. Maquila enviada: suma Cantidad donde Producto contiene HUMEDAS y MAQUILA
        $maquila_enviada = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->whereRaw('UPPER(Producto) LIKE "%HUMEDAS%MAQUILA%"')
            ->sum('Cantidad');

        // 5. Maquila recibida: suma cantidad de bodega del mes
        $maquila_recibida = DB::table('bodega')
            ->where('is_deleted', 0)
            ->whereMonth('fechainicio', $mes)
            ->whereYear('fechainicio', $anio)
            ->sum(DB::raw('CAST(cantidad AS DECIMAL(14,2))'));

        return [
            'saldo_total'      => (float) $saldo_total,
            'total_recepcion'  => (float) $total_recepcion,
            'consumo'          => (float) $consumo,
            'maquila_enviada'  => (float) $maquila_enviada,
            'maquila_recibida' => (float) $maquila_recibida,
        ];
    }

    public static function calcularFormulas(array $valores): array
    {
        $subtotal = $valores['saldo_total'] + $valores['total_recepcion'];
        $saldoSinDescontar = $subtotal - $valores['consumo'];
        $saldoDescontado = $saldoSinDescontar - $valores['maquila_enviada'] - $valores['maquila_recibida'];

        return [
            'subtotal'            => $subtotal,
            'saldo_sin_descontar' => $saldoSinDescontar,
            'saldo_descontado'    => $saldoDescontado,
        ];
    }
}
