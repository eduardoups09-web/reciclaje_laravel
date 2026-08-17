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

    /** Campos que llevan factor de rendimiento (0.95–0.99). */
    public const CAMPOS_CON_FACTOR = ['metalico', 'rejilla', 'metalicofino', 'pastadesulfurada', 'pastasin'];

    /** Valores disponibles para los combobox de factor. */
    public const FACTORES = [0.95, 0.96, 0.97, 0.98, 0.99];

    /** Scope: solo registros no eliminados. */
    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    /** Scope: aplica los filtros del listado (año y mes). */
    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['anio'])) {
            $query->whereYear('fechasalida', $filtros['anio']);
        }
        if (!empty($filtros['mes'])) {
            $query->whereMonth('fechasalida', $filtros['mes']);
        }
        return $query;
    }
}
