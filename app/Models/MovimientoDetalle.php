<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoDetalle extends Model
{
    protected $table = 'movimientodetalle';

    protected $fillable = [
        'fecha', 'grupo', 'turno',
        'status_id', 'is_deleted', 'is_ajuste',
    ];

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];
    public const ESTADOS = [1 => 'Abierto', 2 => 'Cerrado', 4 => 'Aprobado'];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha'])) {
            $query->where('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turno', $filtros['turno']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('grupo', $filtros['grupo']);
        }
        return $query;
    }

    public static function obtenerEstadoActual(string $fecha, string $turno, string $grupo): ?self
    {
        return static::activos()
            ->where('fecha', $fecha)
            ->where('turno', $turno)
            ->where('grupo', $grupo)
            ->orderByDesc('id')
            ->first();
    }
}
