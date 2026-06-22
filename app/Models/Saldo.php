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
        if (!empty($filtros['fecha'])) {
            $query->where('fechasaldo', $filtros['fecha']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('gruposaldo', $filtros['grupo']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turnosaldo', $filtros['turno']);
        }
        return $query;
    }
}
