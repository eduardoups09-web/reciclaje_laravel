<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de SOLO LECTURA sobre la vista `view_consultamovimientos`,
 * que consolida los datos de todas las tablas por fecha + grupo + turno.
 */
class Movimiento extends Model
{
    protected $table = 'view_consultamovimientos';

    /** Es una vista: no tiene timestamps ni se escribe. */
    public $timestamps = false;

    public const GRUPOS = ['1', '2'];
    public const TURNOS = ['Diurno', 'Nocturno'];

    /**
     * Secciones del detalle: título => [columna => etiqueta].
     * Se usa para desglosar la fila consolidada en la vista de detalle.
     */
    public const SECCIONES = [
        'Producción (salidas)' => [
            'salidas_metalico'         => 'Metálico',
            'salidas_rejilla'          => 'Rejilla',
            'salidas_metalicofino'     => 'Metálico fino',
            'salidas_pastadesulfurada' => 'Pasta desulfurada',
            'salidas_pastasin'         => 'Pasta sin desulfurar',
            'salidas_polipropilenokg'  => 'Polipropileno (kg)',
            'salidas_abskg'            => 'ABS (kg)',
            'salidas_separadorkg'      => 'Separador (kg)',
            'salidas_descargas'        => 'Descargas',
        ],
        'Materia prima / Importación' => [
            'pesobateria'       => 'Peso batería',
            'bateriatipo'       => 'Tipo batería',
            'pesobateriaimport' => 'Peso batería (import)',
            'bateriatipoimport' => 'Tipo batería (import)',
            'metalicoimport'    => 'Metálico (import)',
            'pastaimport'       => 'Pasta (import)',
            'placasimport'      => 'Placas (import)',
        ],
        'Insumos' => [
            'carbonatoSodio' => 'Carbonato de sodio',
            'total_consumo'  => 'Consumo total',
        ],
        'Ingresos' => [
            'cantidad_ingresos_nacional'         => 'Nacional',
            'cantidad_ingresos_extranjera'       => 'Extranjera',
            'cantidad_ingresos_automotriz'       => 'Automotriz',
            'cantidad_ingresos_estacionaria'     => 'Estacionaria',
            'cantidad_ingresos_automotriz_nac'   => 'Automotriz (nac)',
            'cantidad_ingresos_estacionaria_nac' => 'Estacionaria (nac)',
            'cantidad_ingresos_maquila'          => 'Maquila',
        ],
        'Bodega y balance' => [
            'cantidad_bodega' => 'Cantidad bodega',
            'total_recepcion' => 'Total recepción',
            'total_despacho'  => 'Total despacho',
            'balance'         => 'Balance',
        ],
        'Saldos' => [
            'salidas_automotriz' => 'Automotriz',
            'salidas_ups'        => 'UPS',
            'salidas_saldos'     => 'Saldo',
        ],
        'Calidad' => [
            'calidad_azufre'  => 'Azufre (%)',
            'calidad_humedad' => 'Humedad (%)',
        ],
    ];

    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['fecha'])) {
            $query->where('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['grupo'])) {
            $query->where('grupo', $filtros['grupo']);
        }
        if (!empty($filtros['turno'])) {
            $query->where('turno', $filtros['turno']);
        }
        return $query;
    }
}
