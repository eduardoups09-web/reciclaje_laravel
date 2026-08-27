<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\Saldosinsert;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only('anio', 'mes');

        if (empty($filtros['anio'])) $filtros['anio'] = now()->year;
        if (empty($filtros['mes']))  $filtros['mes'] = now()->month;

        $existe = DB::table('saldosinsert')
            ->whereYear('fechasaldoinsert', $filtros['anio'])
            ->whereMonth('fechasaldoinsert', $filtros['mes'])
            ->where(function ($q) {
                $q->where('consumo', '>', 0)
                  ->orWhere('total_recepcion', '>', 0);
            })
            ->exists();

        if (!$existe) {
            $this->asegurarRegistrosMes($filtros['anio'], $filtros['mes']);
            $this->calcularYGuardarSaldos($filtros['anio'], $filtros['mes']);
        }

        $aniosDisponibles = Saldosinsert::aniosDisponibles();

        $registros = Saldosinsert::filtrar($filtros)
            ->orderBy('fechasaldoinsert')
            ->orderBy('turnosaldoinsert')
            ->get();

        // Mapear columnas de la tabla a campos que espera el Blade
        $registros->each(function ($r) {
            $r->rec_nac_auto = (float) ($r->recepcion_nacional_automotriz ?? 0);
            $r->rec_nac_ups = (float) ($r->recepcion_nacional_ups ?? 0);
            $r->rec_imp_auto = (float) ($r->recepcion_importada_automotriz ?? 0);
            $r->rec_imp_ups = (float) ($r->recepcion_importada_ups ?? 0);
            $r->total_recepcion_calc = (float) ($r->total_recepcion ?? 0);
            $r->maquila_enviada_calc = (float) ($r->maquila_enviada ?? 0);
            $r->maquila_recibida_calc = (float) ($r->maquila_recibida ?? 0);
            $r->consumo_auto_calc = (float) ($r->bateria_nacional_automotriz ?? 0);
            $r->consumo_ups_calc = (float) ($r->bateria_nacional_ups ?? 0);
            $r->consumo_calc = (float) ($r->consumo ?? 0);

            // Valores diarios para back-calculation del modal
            $r->daily_rec_nac_auto = $r->rec_nac_auto;
            $r->daily_rec_nac_ups = $r->rec_nac_ups;
            $r->daily_rec_imp_auto = $r->rec_imp_auto;
            $r->daily_rec_imp_ups = $r->rec_imp_ups;
            $r->daily_total_recepcion = $r->total_recepcion_calc;
            $r->daily_maquila_enviada = $r->maquila_enviada_calc;
            $r->daily_maquila_recibida = $r->maquila_recibida_calc;
        });

        // Cierre del mes anterior (último registro del mes anterior)
        $anioMesAnt = $filtros['mes'] == 1 ? $filtros['anio'] - 1 : $filtros['anio'];
        $mesAnt = $filtros['mes'] == 1 ? 12 : $filtros['mes'] - 1;
        $cierreMesAnterior = DB::table('saldosinsert')
            ->whereYear('fechasaldoinsert', $anioMesAnt)
            ->whereMonth('fechasaldoinsert', $mesAnt)
            ->orderByDesc('fechasaldoinsert')
            ->orderByDesc('turnosaldoinsert')
            ->first();

        // Cierre del mes actual (último registro del mes filtrado)
        $cierreMesActual = DB::table('saldosinsert')
            ->whereYear('fechasaldoinsert', $filtros['anio'])
            ->whereMonth('fechasaldoinsert', $filtros['mes'])
            ->orderByDesc('fechasaldoinsert')
            ->orderByDesc('turnosaldoinsert')
            ->first();

        // Sumas del mes actual para fila de cierre
        $sumasMesActual = DB::table('saldosinsert')
            ->whereYear('fechasaldoinsert', $filtros['anio'])
            ->whereMonth('fechasaldoinsert', $filtros['mes'])
            ->selectRaw('
                SUM(total_recepcion) as total_recepcion,
                SUM(recepcion_nacional_automotriz) as rec_nac_auto,
                SUM(recepcion_nacional_ups) as rec_nac_ups,
                SUM(recepcion_importada_automotriz) as rec_imp_auto,
                SUM(recepcion_importada_ups) as rec_imp_ups,
                SUM(maquila_enviada) as maquila_enviada,
                SUM(maquila_recibida) as maquila_recibida,
                SUM(consumo) as consumo
            ')
            ->first();

        return view('saldos.index', compact('registros', 'filtros', 'aniosDisponibles', 'cierreMesAnterior', 'cierreMesActual', 'sumasMesActual'));
    }

    public function create()
    {
        return view('saldos.form', ['saldo' => new Saldosinsert(), 'modo' => 'crear']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['gruposaldoinsert'] = $data['turnosaldoinsert'] === 'Diurno' ? '1' : '2';
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $s = Saldosinsert::create($data);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$s->id} creado correctamente.");
    }

    public function edit(Saldosinsert $saldo)
    {
        return view('saldos.form', ['saldo' => $saldo, 'modo' => 'editar']);
    }

    public function update(Request $request, Saldosinsert $saldo)
    {
        $data = $this->validar($request, $saldo);
        $data['gruposaldoinsert'] = $data['turnosaldoinsert'] === 'Diurno' ? '1' : '2';
        $data['updated_at'] = now();
        $saldo->update($data);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$saldo->id} actualizado.");
    }

    public function destroy(Saldosinsert $saldo)
    {
        $saldo->delete();

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$saldo->id} eliminado.");
    }

    /**
     * Auto-llena los saldos de saldosinsert para un mes específico.
     */
    public function autollenar(Request $request)
    {
        $anio = $request->input('anio', now()->year);
        $mes = $request->input('mes', now()->month);

        $this->insertarSaldoInicial();
        $this->asegurarRegistrosMes($anio, $mes);
        $this->calcularYGuardarSaldos($anio, $mes);

        return redirect()->route('saldos.index', ['anio' => $anio, 'mes' => $mes])
            ->with('success', "Se auto-llenaron los saldos del mes.");
    }

    /**
     * Inserta el saldo inicial de enero 2026 si no existe.
     */
    private function insertarSaldoInicial(): void
    {
        $existente = Saldo::where('is_deleted', 0)
            ->where('fechasaldo', '2026-01-01')
            ->first();

        if (!$existente) {
            Saldo::create([
                'fechasaldo'         => '2026-01-01',
                'turnosaldo'         => 'Diurno',
                'gruposaldo'         => '1',
                'cantidadsaldo'      => 868740,
                'cantidadAutomotriz' => 577053,
                'cantidadUPS'        => 291687,
                'status_id'          => 1,
                'is_deleted'         => 0,
                'created_at'         => now(),
            ]);
        }
    }

    /**
     * Asegura que existan registros para cada día del mes × turnos.
     */
    private function asegurarRegistrosMes(int $anio, int $mes): void
    {
        $conteoRegistros = Saldosinsert::whereYear('fechasaldoinsert', $anio)
            ->whereMonth('fechasaldoinsert', $mes)
            ->count();

        $diasMes = Carbon::create($anio, $mes, 1)->daysInMonth;
        $esperados = $diasMes * count(Saldosinsert::TURNOS);

        if ($conteoRegistros > $esperados) {
            Saldosinsert::whereYear('fechasaldoinsert', $anio)
                ->whereMonth('fechasaldoinsert', $mes)
                ->delete();
        }

        $existentes = Saldosinsert::whereYear('fechasaldoinsert', $anio)
            ->whereMonth('fechasaldoinsert', $mes)
            ->select('fechasaldoinsert', 'turnosaldoinsert')
            ->get()
            ->map(fn($r) => $r->fechasaldoinsert . '|' . $r->turnosaldoinsert)
            ->flip()
            ->toArray();

        $aInsertar = [];
        $now = now();

        for ($dia = 1; $dia <= $diasMes; $dia++) {
            $fecha = Carbon::create($anio, $mes, $dia)->toDateString();

            foreach (Saldosinsert::TURNOS as $turno) {
                $key = $fecha . '|' . $turno;

                if (!isset($existentes[$key])) {
                    $aInsertar[] = [
                        'fechasaldoinsert'  => $fecha,
                        'turnosaldoinsert'  => $turno,
                        'gruposaldoinsert'  => $turno === 'Diurno' ? '1' : '2',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
            }
        }

        if (!empty($aInsertar)) {
            foreach (array_chunk($aInsertar, 500) as $chunk) {
                DB::table('saldosinsert')->insert($chunk);
            }
        }
    }

    /**
     * Calcula saldos y valores auxiliares para todo el mes en un solo pase.
     */
    private function calcularYGuardarSaldos(int $anio, int $mes): void
    {
        $registros = Saldosinsert::whereYear('fechasaldoinsert', $anio)
            ->whereMonth('fechasaldoinsert', $mes)
            ->orderBy('fechasaldoinsert')
            ->orderBy('turnosaldoinsert')
            ->get();

        if ($registros->isEmpty()) {
            return;
        }

        $saldoInicial = $this->obtenerSaldoInicial($anio, $mes);

        $saldoTotalAnterior = $saldoInicial['total'];
        $saldoAutoAnterior = $saldoInicial['automotriz'];
        $saldoUpsAnterior = $saldoInicial['ups'];

        $cacheValoresDia = [];
        $now = now();
        $updates = [];

        foreach ($registros as $r) {
            $fecha = $r->fechasaldoinsert;
            $turno = $r->turnosaldoinsert;

            if (!isset($cacheValoresDia[$fecha])) {
                $cacheValoresDia[$fecha] = $this->obtenerValoresDia($fecha);
            }
            $valoresDia = $cacheValoresDia[$fecha];

            $consumo = $this->obtenerConsumo($fecha, $turno);

            $isDiurno = ($turno === 'Diurno');
            $recNacAuto  = $isDiurno ? $valoresDia['rec_nac_auto']  : 0;
            $recImpAuto  = $isDiurno ? $valoresDia['rec_imp_auto']  : 0;
            $recNacUps   = $isDiurno ? $valoresDia['rec_nac_ups']   : 0;
            $recImpUps   = $isDiurno ? $valoresDia['rec_imp_ups']   : 0;
            $totalRecep  = $isDiurno ? $valoresDia['total_recepcion'] : 0;
            $maqEnviada  = $isDiurno ? $valoresDia['maquila_enviada'] : 0;
            $maqRecibida = $isDiurno ? $valoresDia['maquila_recibida'] : 0;

            $maquilaTotal = $maqEnviada + $maqRecibida;
            $totalAuto = $recNacAuto + $recImpAuto;
            $totalUps = $recNacUps + $recImpUps;

            if ($totalRecep == 0 && $maquilaTotal == 0) {
                $saldoTotal = $saldoTotalAnterior - $consumo['total'];
            } else {
                $saldoTotal = $saldoTotalAnterior + ($totalRecep - $maquilaTotal) - $consumo['total'];
            }

            if ($totalAuto == 0 && $maquilaTotal == 0) {
                $saldoAuto = $saldoAutoAnterior - $consumo['auto'];
            } else {
                $saldoAuto = $saldoAutoAnterior + ($totalAuto - $maquilaTotal) - $consumo['auto'];
            }

            $saldoUps = $saldoUpsAnterior + $totalUps - $consumo['ups'];

            $updates[$r->id] = [
                'saldototalinsert'                  => $saldoTotal,
                'saldototalinsertAutomotriz'        => $saldoAuto,
                'saldototalinsertUPS'               => $saldoUps,
                'total_recepcion'                   => $totalRecep,
                'recepcion_nacional_automotriz'     => $recNacAuto,
                'recepcion_nacional_ups'            => $recNacUps,
                'recepcion_importada_automotriz'    => $recImpAuto,
                'recepcion_importada_ups'           => $recImpUps,
                'bateria_nacional_automotriz'       => $consumo['auto'],
                'bateria_nacional_ups'              => $consumo['ups'],
                'bateria_importada_automotriz'      => 0,
                'bateria_importada_ups'             => 0,
                'consumo'                           => $consumo['total'],
                'maquila_enviada'                   => $maqEnviada,
                'maquila_recibida'                  => $maqRecibida,
                'updated_at'                        => $now,
            ];

            $saldoTotalAnterior = $saldoTotal;
            $saldoAutoAnterior = $saldoAuto;
            $saldoUpsAnterior = $saldoUps;
        }

        if (!empty($updates)) {
            foreach (array_chunk($updates, 500, true) as $chunk) {
                foreach ($chunk as $id => $data) {
                    DB::table('saldosinsert')->where('id', $id)->update($data);
                }
            }
        }

        $this->guardarCierreMes($anio, $mes, $saldoTotalAnterior, $saldoAutoAnterior, $saldoUpsAnterior);
    }

    /**
     * Obtiene valores diarios de recepciones y maquila con 1 query optimizada.
     */
    private function obtenerValoresDia(string $fecha): array
    {
        $fechaSiguiente = date('Y-m-d', strtotime($fecha . ' +1 day'));

        $ingresos = DB::table('ingresosinventarios')
            ->where('FechaCab', '>=', $fecha . ' 00:00:00')
            ->where('FechaCab', '<', $fechaSiguiente . ' 00:00:00')
            ->selectRaw('
                SUM(CASE WHEN Producto IN ("Baterías Humedas Nac", "Baterias Humedas Maquila") THEN Cantidad ELSE 0 END) as rec_nac_auto,
                SUM(CASE WHEN Producto = "Baterías Estacionarias Nac" THEN Cantidad ELSE 0 END) as rec_nac_ups,
                SUM(CASE WHEN Producto LIKE "%Golf%" OR Producto LIKE "%Humedas Ext%" OR Producto LIKE "%Húmedas Ext%" THEN Cantidad ELSE 0 END) as rec_imp_auto,
                SUM(CASE WHEN Producto LIKE "Baterías Estacionarias Ext%" THEN Cantidad ELSE 0 END) as rec_imp_ups,
                SUM(Cantidad) as total_recepcion,
                SUM(CASE WHEN Producto = "Baterías Húmedas Maquila" THEN Cantidad ELSE 0 END) as maquila_enviada
            ')->first();

        $maquila_recibida = DB::table('bodega')
            ->where('is_deleted', 0)
            ->where('fechainicio', '>=', $fecha)
            ->where('fechainicio', '<', $fechaSiguiente)
            ->sum(DB::raw('CAST(cantidad AS DECIMAL(14,2))'));

        return [
            'rec_nac_auto'      => (float) ($ingresos->rec_nac_auto ?? 0),
            'rec_nac_ups'       => (float) ($ingresos->rec_nac_ups ?? 0),
            'rec_imp_auto'      => (float) ($ingresos->rec_imp_auto ?? 0),
            'rec_imp_ups'       => (float) ($ingresos->rec_imp_ups ?? 0),
            'total_recepcion'   => (float) ($ingresos->total_recepcion ?? 0),
            'maquila_enviada'   => (float) ($ingresos->maquila_enviada ?? 0),
            'maquila_recibida'  => (float) $maquila_recibida,
        ];
    }

    /**
     * Obtiene consumo por turno con 2 queries optimizadas.
     */
    private function obtenerConsumo(string $fecha, string $turno): array
    {
        $fechaSiguiente = date('Y-m-d', strtotime($fecha . ' +1 day'));

        $nacional = DB::table('mpnacional')
            ->where('is_deleted', 0)
            ->where('fechanacional', '>=', $fecha)
            ->where('fechanacional', '<', $fechaSiguiente)
            ->where('turnonacional', $turno)
            ->selectRaw('
                SUM(CASE WHEN bateriatipo = "Automotriz" THEN pesobateria ELSE 0 END) as auto,
                SUM(CASE WHEN bateriatipo = "UPS" THEN pesobateria ELSE 0 END) as ups
            ')->first();

        $importado = DB::table('mpimport')
            ->where('is_deleted', 0)
            ->where('fechaimport', '>=', $fecha)
            ->where('fechaimport', '<', $fechaSiguiente)
            ->where('turnoimport', $turno)
            ->selectRaw('
                SUM(CASE WHEN bateriatipoimport = "Automotriz" THEN pesobateriaimport ELSE 0 END) as auto,
                SUM(CASE WHEN bateriatipoimport = "UPS" THEN pesobateriaimport ELSE 0 END) as ups
            ')->first();

        $consumoAuto = (float) ($nacional->auto ?? 0) + (float) ($importado->auto ?? 0);
        $consumoUps = (float) ($nacional->ups ?? 0) + (float) ($importado->ups ?? 0);

        return [
            'auto'  => $consumoAuto,
            'ups'   => $consumoUps,
            'total' => $consumoAuto + $consumoUps,
        ];
    }

    /**
     * Obtiene el saldo inicial del mes.
     */
    private function obtenerSaldoInicial(int $anio, int $mes): array
    {
        $fechaInicioMes = Carbon::create($anio, $mes, 1)->toDateString();

        $saldo = Saldo::where('is_deleted', 0)
            ->where('fechasaldo', $fechaInicioMes)
            ->first();

        if ($saldo) {
            return [
                'total'      => (float) $saldo->cantidadsaldo,
                'automotriz' => (float) $saldo->cantidadAutomotriz,
                'ups'        => (float) $saldo->cantidadUPS,
            ];
        }

        $ultimoRegistro = Saldosinsert::where('fechasaldoinsert', '<', $fechaInicioMes)
            ->orderByDesc('fechasaldoinsert')
            ->orderByDesc('turnosaldoinsert')
            ->first();

        if ($ultimoRegistro) {
            return [
                'total'      => (float) $ultimoRegistro->saldototalinsert,
                'automotriz' => (float) $ultimoRegistro->saldototalinsertAutomotriz,
                'ups'        => (float) $ultimoRegistro->saldototalinsertUPS,
            ];
        }

        return [
            'total'      => 0,
            'automotriz' => 0,
            'ups'        => 0,
        ];
    }

    /**
     * Guarda el cierre del mes en la tabla saldos.
     */
    private function guardarCierreMes(int $anio, int $mes, float $saldoTotal, float $saldoAuto, float $saldoUps): void
    {
        $fechaCierre = Carbon::create($anio, $mes, 1)->addMonth()->toDateString();

        $existente = Saldo::where('is_deleted', 0)
            ->where('fechasaldo', $fechaCierre)
            ->first();

        if ($existente) {
            $existente->update([
                'cantidadsaldo'      => $saldoTotal,
                'cantidadAutomotriz' => $saldoAuto,
                'cantidadUPS'        => $saldoUps,
                'updated_at'         => now(),
            ]);
        } else {
            Saldo::create([
                'fechasaldo'         => $fechaCierre,
                'turnosaldo'         => 'Diurno',
                'gruposaldo'         => '1',
                'cantidadsaldo'      => $saldoTotal,
                'cantidadAutomotriz' => $saldoAuto,
                'cantidadUPS'        => $saldoUps,
                'status_id'          => 1,
                'is_deleted'         => 0,
                'created_at'         => now(),
            ]);
        }
    }

    private function validar(Request $request, ?Saldosinsert $saldo = null): array
    {
        $reglas = [
            'fechasaldoinsert' => [
                'required', 'date',
                Rule::unique('saldosinsert')
                    ->where('turnosaldoinsert', $request->input('turnosaldoinsert'))
                    ->ignore($saldo?->id),
            ],
            'turnosaldoinsert' => ['required', 'in:' . implode(',', Saldosinsert::TURNOS)],
        ];
        foreach (array_keys(Saldosinsert::CANTIDADES) as $campo) {
            $reglas[$campo] = ['nullable', 'integer'];
        }

        return $request->validate($reglas, [], [
            'fechasaldoinsert' => 'fecha',
            'turnosaldoinsert' => 'turno',
        ]);
    }
}
