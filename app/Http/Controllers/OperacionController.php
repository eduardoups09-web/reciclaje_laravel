<?php

namespace App\Http\Controllers;

use App\Models\AnalisisCalidad;
use App\Models\Insumo;
use App\Models\MovimientoDetalle;
use App\Models\MpImport;
use App\Models\MpNacional;
use App\Models\Salida;
use Illuminate\Http\Request;

class OperacionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'movdetalle');
        $turnoActual = now()->hour >= 7 && now()->hour < 19 ? 'Diurno' : 'Nocturno';

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?? $turnoActual,
        ];

        $registros = null;
        $recurso = '';

        switch ($tab) {
            case 'produccion':
                $registros = Salida::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechasalida', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnosalida', $v))
                    ->orderByDesc('fechasalida')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'produccion';
                break;

            case 'calidad':
                $registros = AnalisisCalidad::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnocalidad', $v))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'calidad';
                break;

            case 'mpimport':
                $registros = MpImport::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechaimport', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnoimport', $v))
                    ->orderByDesc('fechaimport')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'mpimport';
                break;

            case 'mpnacional':
                $registros = MpNacional::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechanacional', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnonacional', $v))
                    ->orderByDesc('fechanacional')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'mpnacional';
                break;

            case 'insumos':
                $registros = Insumo::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnoinsumo', $v))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'insumos';
                break;

            case 'movdetalle':
                $registros = MovimientoDetalle::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turno', $v))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'movimiento-detalle';
                break;
        }

        return view('operaciones.index', compact('tab', 'filtros', 'registros', 'recurso'));
    }
}
