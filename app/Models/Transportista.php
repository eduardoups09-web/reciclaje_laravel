<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportista extends Model
{
    /** La tabla ya existe en la base `reciclaje_01`. */
    protected $table = 'transportista';

    public $timestamps = true;

    protected $fillable = [
        'transportistas',
        'ruc',
        'placa',
        'fecha',
        'is_deleted',
        'usernameTransportista',
    ];

    public function scopeActivos($query)
    {
        return $query->where('is_deleted', 0);
    }
}
