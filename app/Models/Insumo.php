<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $table = 'insumos';

    protected $fillable = [
        'fecha', 'grupoinsumo', 'turnoinsumo',
        'carbonatoSodio', 'cal',
        'status_id', 'is_deleted', 'is_ajuste', 'usernameinsumo',
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
            $query->where('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turnoinsumo', $filtros['turno']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('grupoinsumo', $filtros['grupo']);
        }
        return $query;
    }
}
