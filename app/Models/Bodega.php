<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    /** La tabla ya existe en la base `reciclaje`. */
    protected $table = 'bodega';

    public $timestamps = true;

    protected $fillable = [
        'fechainicio', 'grupo', 'turno', 'tipobateria', 'contenedor', 'cantidad', 'unidad',
        'consecutivo', 'despacho',
        'nombreDestinatario', 'rucDestinatario',
        'nombreTransportista', 'transportistaRuc', 'placaTransportista',
        'observacion', 'motivo', 'partida', 'fechaentrega',
        'is_deleted', 'usernameBodega',
    ];

    /** Sugerencias para los campos de texto (datalist). */
    public const TIPOS_BATERIA = ['Bateria Chatarra Automotriz', 'Bateria UPS', 'Otro'];
    public const UNIDADES      = ['Kilogramos', 'Unidades'];
    public const GRUPOS        = ['1', '2'];
    public const TURNOS        = ['Diurno', 'Nocturno'];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha'])) {
            $query->where('fechainicio', $filtros['fecha']);
        }
        if (!empty($filtros['tipo'])) {
            $query->where('tipobateria', $filtros['tipo']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('grupo', $filtros['grupo']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turno', $filtros['turno']);
        }
        if (!empty($filtros['buscar'])) {
            $b = $filtros['buscar'];
            $query->where(function ($q) use ($b) {
                $q->where('contenedor', 'like', "%$b%")
                  ->orWhere('nombreDestinatario', 'like', "%$b%")
                  ->orWhere('nombreTransportista', 'like', "%$b%")
                  ->orWhere('consecutivo', 'like', "%$b%");
            });
        }
        return $query;
    }
}
