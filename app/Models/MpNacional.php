<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpNacional extends Model
{
    protected $table = 'mpnacional';

    protected $fillable = [
        'fechanacional', 'gruponacional', 'turnonacional',
        'bateriatipo', 'pesobateria',
        'status_id', 'is_deleted', 'is_ajuste', 'usernamenacional',
    ];

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];
    public const TIPOS_BATERIA = ['Automotriz', 'UPS'];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha'])) {
            $query->where('fechanacional', $filtros['fecha']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turnonacional', $filtros['turno']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('gruponacional', $filtros['grupo']);
        }
        return $query;
    }
}
