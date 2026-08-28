<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteGerencialPablo extends Model
{
    protected $table = 'reporte_gerencial_pablo';

    protected $fillable = [
        'mes', 'anio',
        'saldo_total', 'total_recepcion',
        'recepcion_nacional_automotriz', 'recepcion_nacional_ups',
        'recepcion_importada_automotriz', 'recepcion_importada_ups',
        'bateria_nacional_automotriz', 'bateria_nacional_ups',
        'bateria_importada_automotriz', 'bateria_importada_ups',
        'consumo', 'maquila_enviada', 'maquila_recibida',
        'saldo_cierre_automotriz', 'saldo_cierre_ups',
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

        // 2. Total recepción
        $total_recepcion = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->sum('Cantidad');

        // 3. Recepción Nacional Automotriz
        $recepcion_nacional_automotriz = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->whereIn('Producto', ['Baterías Humedas Nac', 'Baterias Humedas Maquila'])
            ->sum('Cantidad');

        // 4. Recepción Nacional UPS
        $recepcion_nacional_ups = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->where('Producto', 'Baterías Estacionarias Nac')
            ->sum('Cantidad');

        // 5. Recepción Importado Automotriz
        $recepcion_importada_automotriz = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->whereIn('Producto', ['Baterias de Golf', 'Baterías Humedas Ext'])
            ->sum('Cantidad');

        // 6. Recepción Importado UPS
        $recepcion_importada_ups = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->where('Producto', 'LIKE', 'Baterías Estacionarias Ext%')
            ->sum('Cantidad');

        // 7. Batería Nacional Automotriz
        $bateria_nacional_automotriz = DB::table('mpnacional')
            ->where('is_deleted', 0)
            ->whereMonth('fechanacional', $mes)
            ->whereYear('fechanacional', $anio)
            ->where('bateriatipo', 'Automotriz')
            ->sum('pesobateria');

        // 8. Batería Nacional UPS
        $bateria_nacional_ups = DB::table('mpnacional')
            ->where('is_deleted', 0)
            ->whereMonth('fechanacional', $mes)
            ->whereYear('fechanacional', $anio)
            ->where('bateriatipo', 'UPS')
            ->sum('pesobateria');

        // 9. Batería Importada Automotriz
        $bateria_importada_automotriz = DB::table('mpimport')
            ->where('is_deleted', 0)
            ->whereMonth('fechaimport', $mes)
            ->whereYear('fechaimport', $anio)
            ->where('bateriatipoimport', 'Automotriz')
            ->sum('pesobateriaimport');

        // 10. Batería Importada UPS
        $bateria_importada_ups = DB::table('mpimport')
            ->where('is_deleted', 0)
            ->whereMonth('fechaimport', $mes)
            ->whereYear('fechaimport', $anio)
            ->where('bateriatipoimport', 'UPS')
            ->sum('pesobateriaimport');

        // 11. Consumo
        $consumo = $bateria_nacional_automotriz + $bateria_nacional_ups
                 + $bateria_importada_automotriz + $bateria_importada_ups;

        // 12. Maquila enviada
        $maquila_enviada = DB::table('ingresosInventarios')
            ->whereMonth('FechaCab', $mes)
            ->whereYear('FechaCab', $anio)
            ->where('Producto', 'Baterías Húmedas Maquila')
            ->sum('Cantidad');

        // 13. Maquila recibida
        $maquila_recibida = DB::table('bodega')
            ->where('is_deleted', 0)
            ->whereMonth('fechainicio', $mes)
            ->whereYear('fechainicio', $anio)
            ->sum(DB::raw('CAST(cantidad AS DECIMAL(14,2))'));

        // 14. Saldo Cierre Automotriz: directo de tabla saldos día 01
        $saldo_cierre_automotriz = $saldo->cantidadAutomotriz ?? 0;

        // 15. Saldo Cierre UPS: directo de tabla saldos día 01
        $saldo_cierre_ups = $saldo->cantidadUPS ?? 0;

        return [
            'saldo_total'                    => (float) $saldo_total,
            'total_recepcion'                => (float) $total_recepcion,
            'recepcion_nacional_automotriz'  => (float) $recepcion_nacional_automotriz,
            'recepcion_nacional_ups'         => (float) $recepcion_nacional_ups,
            'recepcion_importada_automotriz' => (float) $recepcion_importada_automotriz,
            'recepcion_importada_ups'        => (float) $recepcion_importada_ups,
            'bateria_nacional_automotriz'    => (float) $bateria_nacional_automotriz,
            'bateria_nacional_ups'           => (float) $bateria_nacional_ups,
            'bateria_importada_automotriz'   => (float) $bateria_importada_automotriz,
            'bateria_importada_ups'          => (float) $bateria_importada_ups,
            'consumo'                        => (float) $consumo,
            'maquila_enviada'                => (float) $maquila_enviada,
            'maquila_recibida'               => (float) $maquila_recibida,
            'total_maquila'                  => (float) ($maquila_enviada + $maquila_recibida),
            'saldo_cierre_automotriz'        => (float) $saldo_cierre_automotriz,
            'saldo_cierre_ups'               => (float) $saldo_cierre_ups,
        ];
    }

    public static function calcularFormulas(array $valores): array
    {
        $saldoCierre = $valores['saldo_total']
            + $valores['total_recepcion']
            - $valores['consumo']
            - $valores['maquila_enviada']
            - $valores['maquila_recibida'];

        return [
            'saldo_cierre' => $saldoCierre,
        ];
    }
}
