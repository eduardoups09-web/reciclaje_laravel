<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    /** La tabla ya existe en la base `reciclaje`. */
    protected $table = 'bodega';

    public $timestamps = true;

    protected $fillable = [
        'fechainicio', 'tipobateria', 'contenedor', 'cantidad', 'unidad',
        'consecutivo', 'despacho',
        'nombreDestinatario', 'rucDestinatario',
        'nombreTransportista', 'transportistaRuc', 'placaTransportista',
        'observacion', 'motivo', 'partida', 'fechaemision', 'llegada',
        'is_deleted', 'usernameBodega',
    ];

    /** Sugerencias para los campos de texto (datalist). */
    public const TIPOS_BATERIA = ['Bateria Chatarra Automotriz', 'Bateria UPS', 'Otro'];
    public const UNIDADES      = ['Kilogramos', 'Unidades'];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha_desde'])) {
            $query->where('fechainicio', '>=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fechainicio', '<=', $filtros['fecha_hasta']);
        }
        return $query;
    }
}
