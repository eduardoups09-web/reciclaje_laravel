<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use stdClass;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [
            'anio' => $request->input('anio') ?? now()->year,
            'mes'  => $request->input('mes')  ?? now()->month,
            'tipo_bateria' => $request->input('tipo_bateria') ?? '',
        ];

        $anio = $filtros['anio'];
        $mes  = $filtros['mes'];
        $tipoBateria = $filtros['tipo_bateria'];

        $whereYear  = fn($col) => fn($q) => $q->whereYear($col, $anio);
        $whereMonth = fn($col) => fn($q) => $q->whereMonth($col, $mes);

        // Filtros para mpnacional
        $filterNac = fn($q) => match($tipoBateria) {
            'nac_auto' => $q->where('bateriatipo', 'Automotriz'),
            'nac_ups'  => $q->where('bateriatipo', 'UPS'),
            default    => $q,
        };

        // Filtros para mpimport
        $filterImp = fn($q) => match($tipoBateria) {
            'imp_auto' => $q->where('bateriatipoimport', 'Automotriz'),
            'imp_ups'  => $q->where('bateriatipoimport', 'UPS'),
            'met_imp'  => $q->where('metalicoimport', '>', 0),
            'pasta'    => $q->where('pastaimport', '>', 0),
            'placas'   => $q->where('placasimport', '>', 0),
            default    => $q,
        };

        // Construir UNION condicional según filtro de batería
        if (in_array($tipoBateria, ['nac_auto', 'nac_ups'])) {
            $unionQuery = DB::table('mpnacional')
                ->where('is_deleted', 0)
                ->when($anio, $whereYear('fechanacional'))
                ->when($mes, $whereMonth('fechanacional'))
                ->when($tipoBateria, $filterNac)
                ->selectRaw("DATE_FORMAT(fechanacional,'%Y-%m-%d') as fecha, turnonacional as turno");
        } elseif (in_array($tipoBateria, ['imp_auto', 'imp_ups', 'met_imp', 'pasta', 'placas'])) {
            $unionQuery = DB::table('mpimport')
                ->where('is_deleted', 0)
                ->when($anio, $whereYear('fechaimport'))
                ->when($mes, $whereMonth('fechaimport'))
                ->when($tipoBateria, $filterImp)
                ->selectRaw("DATE_FORMAT(fechaimport,'%Y-%m-%d') as fecha, turnoimport as turno");
        } else {
            $unionQuery = DB::table('movimientodetalle')
                ->where('is_deleted', 0)
                ->when($anio, $whereYear('fecha'))
                ->when($mes, $whereMonth('fecha'))
                ->selectRaw("DATE_FORMAT(fecha,'%Y-%m-%d') as fecha, turno")
                ->unionAll(
                    DB::table('mpnacional')->where('is_deleted', 0)
                        ->when($anio, $whereYear('fechanacional'))
                        ->when($mes, $whereMonth('fechanacional'))
                        ->selectRaw("DATE_FORMAT(fechanacional,'%Y-%m-%d') as fecha, turnonacional as turno")
                )
                ->unionAll(
                    DB::table('mpimport')->where('is_deleted', 0)
                        ->when($anio, $whereYear('fechaimport'))
                        ->when($mes, $whereMonth('fechaimport'))
                        ->selectRaw("DATE_FORMAT(fechaimport,'%Y-%m-%d') as fecha, turnoimport as turno")
                )
                ->unionAll(
                    DB::table('insumos')->where('is_deleted', 0)
                        ->when($anio, $whereYear('fecha'))
                        ->when($mes, $whereMonth('fecha'))
                        ->selectRaw("DATE_FORMAT(fecha,'%Y-%m-%d') as fecha, turnoinsumo as turno")
                )
                ->unionAll(
                    DB::table('salidas')->where('is_deleted', 0)
                        ->when($anio, $whereYear('fechasalida'))
                        ->when($mes, $whereMonth('fechasalida'))
                        ->selectRaw("DATE_FORMAT(fechasalida,'%Y-%m-%d') as fecha, turnosalida as turno")
                )
                ->unionAll(
                    DB::table('analisiscalidad')->where('is_deleted', 0)
                        ->when($anio, $whereYear('fecha'))
                        ->when($mes, $whereMonth('fecha'))
                        ->selectRaw("DATE_FORMAT(fecha,'%Y-%m-%d') as fecha, turnocalidad as turno")
                );
        }

        $fechasUnicas = $unionQuery
            ->distinct()
            ->orderBy('fecha')
            ->orderBy('turno')
            ->get()
            ->keyBy(fn($r) => "{$r->fecha}-{$r->turno}");

        $registros = $fechasUnicas;

        if ($registros->isEmpty()) {
            return view('movimientos.index', [
                'registros' => new Collection(),
                'filtros'   => $filtros,
                'anios'     => $this->getAnios(),
            ]);
        }

        $keys = $registros->keys();

        $nacMap = DB::table('mpnacional')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fechanacional'))
            ->when($mes, $whereMonth('fechanacional'))
            ->when(in_array($tipoBateria, ['nac_auto', 'nac_ups']), $filterNac)
            ->selectRaw("CONCAT(DATE_FORMAT(fechanacional,'%Y-%m-%d'),'-',turnonacional) as k,
                SUM(pesobateria) as pesobateria,
                GROUP_CONCAT(DISTINCT bateriatipo SEPARATOR ', ') as bateriatipo")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $impMap = DB::table('mpimport')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fechaimport'))
            ->when($mes, $whereMonth('fechaimport'))
            ->when(in_array($tipoBateria, ['imp_auto', 'imp_ups', 'met_imp', 'pasta', 'placas']), $filterImp)
            ->selectRaw("CONCAT(DATE_FORMAT(fechaimport,'%Y-%m-%d'),'-',turnoimport) as k,
                SUM(pesobateriaimport) as pesobateriaimport,
                GROUP_CONCAT(DISTINCT bateriatipoimport SEPARATOR ', ') as bateriatipoimport,
                SUM(metalicoimport) as metalicoimport,
                SUM(pastaimport) as pastaimport,
                SUM(placasimport) as placasimport")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $insMap = DB::table('insumos')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fecha'))
            ->when($mes, $whereMonth('fecha'))
            ->selectRaw("CONCAT(DATE_FORMAT(fecha,'%Y-%m-%d'),'-',turnoinsumo) as k,
                SUM(carbonatoSodio) as carbonatoSodio")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $salMap = DB::table('salidas')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fechasalida'))
            ->when($mes, $whereMonth('fechasalida'))
            ->selectRaw("CONCAT(DATE_FORMAT(fechasalida,'%Y-%m-%d'),'-',turnosalida) as k,
                SUM(metalico * COALESCE(calculablemeta, 0.97)) as salidas_metalico,
                SUM(rejilla * COALESCE(calculablereji, 0.97)) as salidas_rejilla,
                SUM(metalicofino * COALESCE(calculablemetafino, 0.97)) as salidas_metalicofino,
                SUM(pastadesulfurada * COALESCE(calculablepasta, 0.97)) as salidas_pastadesulfurada,
                SUM(pastasin * COALESCE(calculablepastasin, 0.97)) as salidas_pastasin,
                SUM(polipropilenokg) as polipropilenokg,
                SUM(abskg) as abskg,
                SUM(separadorkg) as separadorkg,
                SUM(descargas) as descargas")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $calMap = DB::table('analisiscalidad')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fecha'))
            ->when($mes, $whereMonth('fecha'))
            ->selectRaw("CONCAT(DATE_FORMAT(fecha,'%Y-%m-%d'),'-',turnocalidad) as k,
                AVG(azufre) as azufre,
                AVG(humedad) as humedad")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $promedioCalidad = DB::table('analisiscalidad')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fecha'))
            ->when($mes, $whereMonth('fecha'))
            ->selectRaw('AVG(NULLIF(azufre, 0)) as azufre, AVG(NULLIF(humedad, 0)) as humedad')
            ->first();

        $statusMap = DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fecha'))
            ->when($mes, $whereMonth('fecha'))
            ->selectRaw("CONCAT(DATE_FORMAT(fecha,'%Y-%m-%d'),'-',turno) as k,
                MAX(status_id) as status_id")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $grupoMap = DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->when($anio, $whereYear('fecha'))
            ->when($mes, $whereMonth('fecha'))
            ->selectRaw("CONCAT(DATE_FORMAT(fecha,'%Y-%m-%d'),'-',turno) as k,
                GROUP_CONCAT(DISTINCT grupo ORDER BY grupo SEPARATOR ', ') as grupo")
            ->groupBy('k')
            ->get()
            ->keyBy('k');

        $consolidados = $registros->map(function ($r) use ($nacMap, $impMap, $insMap, $salMap, $calMap, $statusMap, $grupoMap) {
            $k = "{$r->fecha}-{$r->turno}";
            $nac = $nacMap->get($k);
            $imp = $impMap->get($k);
            $ins = $insMap->get($k);
            $sal = $salMap->get($k);
            $cal = $calMap->get($k);
            $status = $statusMap->get($k);
            $grupo = $grupoMap->get($k);

            $m = new stdClass();
            $m->fecha    = $r->fecha;
            $m->turno    = $r->turno;
            $m->grupo    = $grupo->grupo ?? '-';
            $m->status_id = $status->status_id ?? 1;

            $m->pesobateria      = $nac->pesobateria ?? 0;
            $m->bateriatipo       = $nac->bateriatipo ?? '';

            $m->pesobateriaimport = $imp->pesobateriaimport ?? 0;
            $m->bateriatipoimport = $imp->bateriatipoimport ?? '';
            $m->metalicoimport    = $imp->metalicoimport ?? 0;
            $m->pastaimport       = $imp->pastaimport ?? 0;
            $m->placasimport      = $imp->placasimport ?? 0;

            $m->carbonatoSodio    = $ins->carbonatoSodio ?? 0;

            $m->salidas_metalico         = $sal->salidas_metalico ?? 0;
            $m->salidas_rejilla          = $sal->salidas_rejilla ?? 0;
            $m->salidas_metalicofino     = $sal->salidas_metalicofino ?? 0;
            $m->salidas_pastadesulfurada = $sal->salidas_pastadesulfurada ?? 0;
            $m->salidas_pastasin         = $sal->salidas_pastasin ?? 0;
            $m->salidas_polipropilenokg  = $sal->polipropilenokg ?? 0;
            $m->salidas_abskg            = $sal->abskg ?? 0;
            $m->salidas_separadorkg      = $sal->separadorkg ?? 0;
            $m->salidas_descargas        = $sal->descargas ?? 0;

            $m->calidad_azufre  = $cal->azufre ?? 0;
            $m->calidad_humedad = $cal->humedad ?? 0;

            return $m;
        })->sortBy(fn($r) => $r->fecha . '-' . $r->turno)
          ->values();

        return view('movimientos.index', [
            'registros' => $consolidados,
            'all'       => $consolidados,
            'promedioCalidad' => $promedioCalidad,
            'filtros'   => $filtros,
            'anios'     => $this->getAnios(),
        ]);
    }

    public function show(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'grupo' => ['required', 'string'],
            'turno' => ['required', 'string'],
        ]);

        $where = [
            'fecha'  => $data['fecha'],
            'grupo'  => $data['grupo'],
            'turno'  => $data['turno'],
        ];

        $m = new stdClass();
        $m->fecha    = $data['fecha'];
        $m->grupo    = $data['grupo'];
        $m->turno    = $data['turno'];

        $base = DB::table('movimientodetalle')
            ->where($where)
            ->where('is_deleted', 0)
            ->first();
        $m->status_id = $base->status_id ?? 1;

        $nac = DB::table('mpnacional')
            ->where('fechanacional', $data['fecha'])
            ->where('gruponacional', $data['grupo'])
            ->where('turnonacional', $data['turno'])
            ->where('is_deleted', 0)
            ->selectRaw("SUM(pesobateria) as pesobateria, GROUP_CONCAT(DISTINCT bateriatipo SEPARATOR ', ') as bateriatipo")
            ->first();
        $m->pesobateria = $nac->pesobateria ?? 0;
        $m->bateriatipo = $nac->bateriatipo ?? '';

        $imp = DB::table('mpimport')
            ->where('fechaimport', $data['fecha'])
            ->where('grupoimport', $data['grupo'])
            ->where('turnoimport', $data['turno'])
            ->where('is_deleted', 0)
            ->selectRaw("SUM(pesobateriaimport) as pesobateriaimport, GROUP_CONCAT(DISTINCT bateriatipoimport SEPARATOR ', ') as bateriatipoimport, SUM(metalicoimport) as metalicoimport, SUM(pastaimport) as pastaimport, SUM(placasimport) as placasimport")
            ->first();
        $m->pesobateriaimport = $imp->pesobateriaimport ?? 0;
        $m->bateriatipoimport = $imp->bateriatipoimport ?? '';
        $m->metalicoimport    = $imp->metalicoimport ?? 0;
        $m->pastaimport       = $imp->pastaimport ?? 0;
        $m->placasimport      = $imp->placasimport ?? 0;

        $ins = DB::table('insumos')
            ->where('fecha', $data['fecha'])
            ->where('grupoinsumo', $data['grupo'])
            ->where('turnoinsumo', $data['turno'])
            ->where('is_deleted', 0)
            ->selectRaw('SUM(carbonatoSodio) as carbonatoSodio')
            ->first();
        $m->carbonatoSodio = $ins->carbonatoSodio ?? 0;

        $sal = DB::table('salidas')
            ->where('fechasalida', $data['fecha'])
            ->where('gruposalida', $data['grupo'])
            ->where('turnosalida', $data['turno'])
            ->where('is_deleted', 0)
            ->selectRaw('
                SUM(metalico * COALESCE(calculablemeta, 0.97)) as salidas_metalico,
                SUM(rejilla * COALESCE(calculablereji, 0.97)) as salidas_rejilla,
                SUM(metalicofino * COALESCE(calculablemetafino, 0.97)) as salidas_metalicofino,
                SUM(pastadesulfurada * COALESCE(calculablepasta, 0.97)) as salidas_pastadesulfurada,
                SUM(pastasin * COALESCE(calculablepastasin, 0.97)) as salidas_pastasin,
                SUM(polipropilenokg) as polipropilenokg,
                SUM(abskg) as abskg,
                SUM(separadorkg) as separadorkg,
                SUM(descargas) as descargas
            ')
            ->first();
        $m->salidas_metalico         = $sal->salidas_metalico ?? 0;
        $m->salidas_rejilla          = $sal->salidas_rejilla ?? 0;
        $m->salidas_metalicofino     = $sal->salidas_metalicofino ?? 0;
        $m->salidas_pastadesulfurada = $sal->salidas_pastadesulfurada ?? 0;
        $m->salidas_pastasin         = $sal->salidas_pastasin ?? 0;
        $m->salidas_polipropilenokg  = $sal->polipropilenokg ?? 0;
        $m->salidas_abskg            = $sal->abskg ?? 0;
        $m->salidas_separadorkg      = $sal->separadorkg ?? 0;
        $m->salidas_descargas        = $sal->descargas ?? 0;

        $cal = DB::table('analisiscalidad')
            ->where('fecha', $data['fecha'])
            ->where('grupocalidad', $data['grupo'])
            ->where('turnocalidad', $data['turno'])
            ->where('is_deleted', 0)
            ->selectRaw('AVG(azufre) as azufre, AVG(humedad) as humedad')
            ->first();
        $m->calidad_azufre  = $cal->azufre ?? 0;
        $m->calidad_humedad = $cal->humedad ?? 0;

        return view('movimientos.show', ['m' => $m]);
    }

    private function getAnios()
    {
        return DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->whereYear('fecha', '>', 2000)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'turno' => ['required', 'string'],
        ]);

        $fecha = $data['fecha'];
        $turno = $data['turno'];

        DB::table('movimientodetalle')
            ->where('fecha', $fecha)
            ->where('turno', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        DB::table('mpnacional')
            ->where('fechanacional', $fecha)
            ->where('turnonacional', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        DB::table('mpimport')
            ->where('fechaimport', $fecha)
            ->where('turnoimport', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        DB::table('insumos')
            ->where('fecha', $fecha)
            ->where('turnoinsumo', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        DB::table('salidas')
            ->where('fechasalida', $fecha)
            ->where('turnosalida', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        DB::table('analisiscalidad')
            ->where('fecha', $fecha)
            ->where('turnocalidad', $turno)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => 1]);

        return redirect()->route('movimientos.index')
            ->with('success', 'Registros eliminados correctamente.');
    }
}
