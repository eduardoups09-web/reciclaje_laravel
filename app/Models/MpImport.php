<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpImport extends Model
{
    protected $table = 'mpimport';

    protected $fillable = [
        'fechaimport', 'grupoimport', 'turnoimport',
        'bateriatipoimport', 'pesobateriaimport',
        'metalicoimport', 'pastaimport', 'placasimport',
        'status_id', 'is_deleted', 'is_ajuste', 'usernameimport',
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
            $query->where('fechaimport', $filtros['fecha']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turnoimport', $filtros['turno']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('grupoimport', $filtros['grupo']);
        }
        return $query;
    }
}
