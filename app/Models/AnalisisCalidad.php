<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisCalidad extends Model
{
    /** La tabla ya existe en la base `reciclaje`. */
    protected $table = 'analisiscalidad';

    public $timestamps = true;

    protected $fillable = [
        'fecha', 'turnocalidad', 'grupocalidad', 'reactor', 'filtro', 'hora',
        'reactor1', 'reactor2', 'reactor3', 'reactor4',
        'temperatura', 'ph', 'azufre', 'humedad', 'pi', 'pf',
        'is_deleted', 'usernamecalidad',
    ];

    /** Mediciones decimales: clave => [etiqueta, paso]. */
    public const MEDICIONES = [
        'temperatura' => ['Temperatura (°C)', '0.01'],
        'ph'          => ['pH', '0.01'],
        'azufre'      => ['Azufre (%)', '0.01'],
        'humedad'     => ['Humedad (%)', '0.01'],
    ];

    public const GRUPOS   = ['1', '2'];
    public const TURNOS   = ['Diurno', 'Nocturno'];
    public const REACTORES = ['Reactor#1', 'Reactor#2', 'Reactor#3', 'Reactor#4'];
    public const FILTROS   = ['Filtro#1', 'Filtro#2'];

    /** Devuelve los nombres de reactores activos separados por coma. */
    public function getReactoresNombresAttribute(): string
    {
        $nombres = [];
        foreach (self::REACTORES as $i => $nombre) {
            if (!empty($this->{'reactor' . ($i + 1)})) {
                $nombres[] = $nombre;
            }
        }
        return implode(', ', $nombres);
    }

    /** Scope: solo registros no eliminados. */
    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }

    /** Scope: aplica filtros del listado (año, mes). */
    public function scopeFiltrar($query, array $filtros)
    {
        if (!empty($filtros['anio'])) {
            $query->whereYear('fecha', $filtros['anio']);
        }
        if (!empty($filtros['mes'])) {
            $query->whereMonth('fecha', $filtros['mes']);
        }
        return $query;
    }

    /** Devuelve la hora en formato HH:MM para inputs (la BD guarda HH:MM:SS.microsegundos). */
    public function getHoraCortaAttribute(): string
    {
        return $this->hora ? substr($this->hora, 0, 5) : '';
    }
}
