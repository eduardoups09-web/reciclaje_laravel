<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngresoRc extends Model
{
    protected $table = 'ingresosrc';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'fecha', 'grupo', 'turno', 'status_id', 'is_ajuste',
        'salidas_metalico', 'salidas_rejilla', 'salidas_metalicofino',
        'salidas_pastadesulfurada', 'salidas_pastasin', 'carbonatoSodio',
    ];
}
