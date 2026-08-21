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

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?: 'Diurno',
            'grupo' => $request->input('grupo') ?: '1',
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
                $totales = $this->calcularTotalesProduccion($filtros);
                break;

            case 'calidad':
                $registros = AnalisisCalidad::activos()
                    ->when($filtros['fecha'], fn($q, $v) => $q->where('fecha', $v))
                    ->when($filtros['turno'], fn($q, $v) => $q->where('turnocalidad', $v))
                    ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where(function ($q2) use ($v) {
                        $q2->where('grupocalidad', $v)->orWhereNull('grupocalidad');
                    }))
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

        $totales = $tab === 'produccion' ? ($totales ?? null) : null;

        return view('operaciones.index', compact('tab', 'filtros', 'registros', 'recurso', 'totales'));
    }

    /**
     * Calcula los totales de producción para los filtros actuales.
     */
    private function calcularTotalesProduccion(array $filtros): array
    {
        $totales = [];
        $granCalculado = 0;

        $query = Salida::activos()
            ->when($filtros['fecha'], fn($q, $v) => $q->where('fechasalida', $v))
            ->when($filtros['turno'], fn($q, $v) => $q->where('turnosalida', $v))
            ->when(!empty($filtros['grupo']), fn($q, $v) => $q->where('gruposalida', $filtros['grupo']));

        foreach (Salida::CAMPOS_FACTOR_MAP as $campo => $columna) {
            $resultado = (clone $query)
                ->selectRaw("SUM(COALESCE({$campo}, 0) * COALESCE({$columna}, 0.97)) as calculado")
                ->first();

            $calculado = round((float) ($resultado->calculado ?? 0));
            $totales[$campo] = ['calculado' => $calculado];
            $granCalculado += $calculado;
        }

        $camposSinFactor = ['polipropilenokg', 'abskg', 'separadorkg', 'descargas'];
        foreach ($camposSinFactor as $campo) {
            $totales[$campo] = (int) (clone $query)->sum($campo);
        }

        $totales['gran_total'] = ['calculado' => $granCalculado];

        return $totales;
    }
}
