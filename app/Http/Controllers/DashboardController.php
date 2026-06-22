<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today()->toDateString();

        $datos = [
            'salidasHoy'       => Salida::activos()->where('fechasalida', $hoy)->count(),
            'totalSalidas'     => Salida::activos()->count(),
            'totalMovimientos' => \DB::table('movimientodetalle')->where('is_deleted', 0)->count(),
            'pastaSemana'      => (int) Salida::activos()
                ->where('fechasalida', '>=', Carbon::today()->subDays(7)->toDateString())
                ->sum('pastadesulfurada'),
        ];

        return view('dashboard', $datos);
    }
}
