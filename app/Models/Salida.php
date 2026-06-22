<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    /** La tabla ya existe en la base `reciclaje`. */
    protected $table = 'salidas';

    /** La tabla tiene created_at / updated_at, así que mantenemos timestamps. */
    public $timestamps = true;

    protected $fillable = [
        'fechasalida', 'gruposalida', 'turnosalida',
        'metalico', 'rejilla', 'metalicofino', 'pastadesulfurada', 'pastasin',
        'polipropilenokg', 'abskg', 'separadorkg', 'descargas',
        'status_id', 'is_deleted', 'usernameproduccion',
    ];

    /** Campos numéricos editables: clave => etiqueta. */
    public const CAMPOS_NUMERICOS = [
        'metalico'         => 'Metálico',
        'rejilla'          => 'Rejilla',
        'metalicofino'     => 'Metálico fino',
        'pastadesulfurada' => 'Pasta desulfurada',
        'pastasin'         => 'Pasta sin desulfurar',
        'polipropilenokg'  => 'Polipropileno (kg)',
        'abskg'            => 'ABS (kg)',
        'separadorkg'      => 'Separador (kg)',
        'descargas'        => 'Descargas',
    ];

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];

    /** Scope: solo registros no eliminados. */
    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    /** Scope: aplica los filtros del listado (fecha, grupo, turno). */
    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha'])) {
            $query->where('fechasalida', $filtros['fecha']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('gruposalida', $filtros['grupo']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turnosalida', $filtros['turno']);
        }
        return $query;
    }
}
