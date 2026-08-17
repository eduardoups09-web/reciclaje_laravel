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
            'turno' => $tab === 'movdetalle' ? $request->input('turno') : ($request->input('turno') ?? $turnoActual),
            'grupo' => $request->input('grupo'),
        ];

        $registros = null;
        $recurso = '';

        switch ($tab) {
            case 'produccion':
                $registros = Salida::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechasalida', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnosalida', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('gruposalida', $filtros['grupo']))
                    ->orderByDesc('fechasalida')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'produccion';
                break;

            case 'calidad':
                $registros = AnalisisCalidad::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnocalidad', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('grupocalidad', $filtros['grupo']))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'calidad';
                break;

            case 'mpimport':
                $registros = MpImport::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechaimport', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnoimport', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('grupoimport', $filtros['grupo']))
                    ->orderByDesc('fechaimport')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'mpimport';
                break;

            case 'mpnacional':
                $registros = MpNacional::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fechanacional', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnonacional', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('gruponacional', $filtros['grupo']))
                    ->orderByDesc('fechanacional')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'mpnacional';
                break;

            case 'insumos':
                $registros = Insumo::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnoinsumo', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('grupoinsumo', $filtros['grupo']))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'insumos';
                break;

            case 'movdetalle':
                $registros = MovimientoDetalle::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when(!empty($filtros['turno']), fn($q, $v) => $q->where('turno', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('grupo', $filtros['grupo']))
                    ->orderByDesc('fecha')->orderByDesc('id')
                    ->paginate(25)->withQueryString();
                $recurso = 'movimiento-detalle';
                break;
        }

        return view('operaciones.index', compact('tab', 'filtros', 'registros', 'recurso'));
    }
}
