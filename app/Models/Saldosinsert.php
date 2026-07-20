<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldosinsert extends Model
{
    protected $table = 'saldosinsert';
    public $timestamps = false;

    protected $fillable = [
        'fechasaldoinsert', 'turnosaldoinsert', 'gruposaldoinsert',
        'saldototalinsert', 'saldototalinsertAutomotriz', 'saldototalinsertUPS',
        'total_recepcion',
        'recepcion_nacional_automotriz', 'recepcion_nacional_ups',
        'recepcion_importada_automotriz', 'recepcion_importada_ups',
        'bateria_nacional_automotriz', 'bateria_nacional_ups',
        'bateria_importada_automotriz', 'bateria_importada_ups',
        'consumo', 'maquila_enviada', 'maquila_recibida',
        'saldo_cierre', 'saldo_cierre_automotriz', 'saldo_cierre_ups',
        'created_at', 'updated_at',
    ];

    /** Cantidades editables: clave => etiqueta. */
    public const CANTIDADES = [
        'saldototalinsert'           => 'Saldo total',
        'saldototalinsertAutomotriz' => 'Automotriz',
        'saldototalinsertUPS'        => 'UPS',
    ];

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['anio']) && !empty($filtros['mes'])) {
            $query->whereYear('fechasaldoinsert', $filtros['anio'])
                  ->whereMonth('fechasaldoinsert', $filtros['mes']);
        }
        return $query;
    }

    /**
     * Retorna array de años disponibles: rango fijo + años con registros en la tabla.
     */
    public static function aniosDisponibles(): array
    {
        $aniosBase = range(2024, now()->year);

        $aniosConRegistros = static::selectRaw('DISTINCT YEAR(fechasaldoinsert) as anio')
            ->pluck('anio')
            ->toArray();

        return array_unique(array_merge($aniosBase, $aniosConRegistros));
    }
}
