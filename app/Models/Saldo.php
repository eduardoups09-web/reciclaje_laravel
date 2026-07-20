<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    /** La tabla ya existe en la base `reciclaje`. */
    protected $table = 'saldos';

    /**
     * En esta tabla `created_at` es de tipo DATE (no datetime), así que
     * desactivamos el manejo automático de timestamps para no chocar con su formato.
     */
    public $timestamps = false;

    protected $fillable = [
        'cantidadsaldo', 'cantidadAutomotriz', 'cantidadUPS',
        'fechasaldo', 'turnosaldo', 'gruposaldo',
        'status_id', 'is_deleted', 'is_static', 'is_ajuste',
        'created_at', 'updated_at',
    ];

    /** Cantidades editables (pueden ser negativas): clave => etiqueta. */
    public const CANTIDADES = [
        'cantidadsaldo'      => 'Saldo total',
        'cantidadAutomotriz' => 'Automotriz',
        'cantidadUPS'        => 'UPS',
    ];

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['anio']) && !empty($filtros['mes'])) {
            $query->whereYear('fechasaldo', $filtros['anio'])
                  ->whereMonth('fechasaldo', $filtros['mes']);
        }
        return $query;
    }

    /**
     * Retorna array de años que tienen registros en la tabla.
     */
    public static function aniosDisponibles(): array
    {
        return static::activos()
            ->selectRaw('DISTINCT YEAR(fechasaldo) as anio')
            ->orderBy('anio')
            ->pluck('anio')
            ->toArray();
    }
}
