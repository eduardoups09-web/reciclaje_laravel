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
        $this->asegurarColumnasSaldosinsert();

        $filtros = $request->only('anio', 'mes');

        if (empty($filtros['anio'])) $filtros['anio'] = now()->year;
        if (empty($filtros['mes']))  $filtros['mes'] = now()->month;

        $this->asegurarRegistrosMes($filtros['anio'], $filtros['mes']);

        $columnasExistentes = $this->verificarColumnasSaldosinsert();

        $existe = false;
        if ($columnasExistentes) {
            $existe = DB::table('saldosinsert')
                ->whereYear('fechasaldoinsert', $filtros['anio'])
                ->whereMonth('fechasaldoinsert', $filtros['mes'])
                ->where(function ($q) {
                    $q->where('consumo', '>', 0)
                      ->orWhere('total_recepcion', '>', 0)
                      ->orWhere('maquila_recibida', '>', 0);
                })
                ->exists();
        }

        $bodegaSinReflejar = false;
        if ($columnasExistentes) {
            $bodegaSinReflejar = DB::table('bodega')
                ->selectRaw('fechainicio, SUM(CAST(cantidad AS DECIMAL(14,2))) as total_bodega')
                ->where('is_deleted', 0)
                ->whereYear('fechainicio', $filtros['anio'])
                ->whereMonth('fechainicio', $filtros['mes'])
                ->whereNotNull('fechainicio')
                ->groupBy('fechainicio')
                ->get()
                ->filter(function ($b) {
                    $saldoMaquila = DB::table('saldosinsert')
                        ->where('fechasaldoinsert', $b->fechainicio)
                        ->where('turnosaldoinsert', 'Diurno')
                        ->value('maquila_recibida');
                    return (float) ($saldoMaquila ?? 0) != $b->total_bodega;
                })
                ->isNotEmpty();
        }

        if (!$existe || $bodegaSinReflejar) {
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

        $anio = Carbon::parse($s->fechasaldoinsert)->year;
        $mes = Carbon::parse($s->fechasaldoinsert)->month;
        $this->calcularYGuardarSaldos($anio, $mes);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$s->id} creado y recalculado correctamente.");
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

        $anio = Carbon::parse($saldo->fechasaldoinsert)->year;
        $mes = Carbon::parse($saldo->fechasaldoinsert)->month;
        $this->calcularYGuardarSaldos($anio, $mes);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$saldo->id} actualizado y recalculado.");
    }

    public function destroy(Saldosinsert $saldo)
    {
        $anio = Carbon::parse($saldo->fechasaldoinsert)->year;
        $mes = Carbon::parse($saldo->fechasaldoinsert)->month;
        
        $saldo->delete();

        $this->calcularYGuardarSaldos($anio, $mes);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo eliminado y mes recalculado.");
    }

    /**
     * Auto-llena los saldos de saldosinsert para un mes específico.
     */
    public function autollenar(Request $request)
    {
        $anio = $request->input('anio', now()->year);
        $mes = $request->input('mes', now()->month);

        $this->asegurarRegistrosMes($anio, $mes);
        $this->calcularYGuardarSaldos($anio, $mes);

        return redirect()->route('saldos.index', ['anio' => $anio, 'mes' => $mes])
            ->with('success', "Se auto-llenaron los saldos del mes.");
    }

    /**
     * Asegura que existan registros para cada día del mes × turnos.
     */
    private function asegurarRegistrosMes(int $anio, int $mes): void
    {
        $now = now();

        $fechasMov = DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->selectRaw('DISTINCT fecha, turno')
            ->get()
            ->keyBy(fn($r) => $r->fecha . '|' . $r->turno);

        $fechasBodega = DB::table('bodega')
            ->where('is_deleted', 0)
            ->whereYear('fechainicio', $anio)
            ->whereMonth('fechainicio', $mes)
            ->whereNotNull('fechainicio')
            ->selectRaw('DISTINCT fechainicio as fecha')
            ->get()
            ->keyBy(fn($r) => $r->fecha);

        $aInsertar = [];
        foreach ($fechasMov as $fm) {
            $existe = DB::table('saldosinsert')
                ->where('fechasaldoinsert', $fm->fecha)
                ->where('turnosaldoinsert', $fm->turno)
                ->exists();

            if (!$existe) {
                $aInsertar[] = [
                    'fechasaldoinsert'  => $fm->fecha,
                    'turnosaldoinsert'  => $fm->turno,
                    'gruposaldoinsert'  => $fm->turno === 'Diurno' ? '1' : '2',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }

        foreach ($fechasBodega as $fb) {
            $key = $fb->fecha . '|Diurno';
            if (!isset($fechasMov[$key])) {
                $existe = DB::table('saldosinsert')
                    ->where('fechasaldoinsert', $fb->fecha)
                    ->where('turnosaldoinsert', 'Diurno')
                    ->exists();

                if (!$existe) {
                    $aInsertar[] = [
                        'fechasaldoinsert'  => $fb->fecha,
                        'turnosaldoinsert'  => 'Diurno',
                        'gruposaldoinsert'  => '1',
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
        $this->asegurarColumnasSaldosinsert();

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

        $ingresos = DB::table('ingresosInventarios')
            ->where('FechaCab', '>=', $fecha . ' 00:00:00')
            ->where('FechaCab', '<', $fechaSiguiente . ' 00:00:00')
            ->selectRaw('
                SUM(CASE WHEN UPPER(Producto) IN ("BATERIAS HUMEDAS NAC", "BATERIAS HUMEDAS MAQUILA") THEN Cantidad ELSE 0 END) as rec_nac_auto,
                SUM(CASE WHEN UPPER(Producto) = "BATERIAS ESTACIONARIAS NAC" THEN Cantidad ELSE 0 END) as rec_nac_ups,
                SUM(CASE WHEN UPPER(Producto) LIKE "%GOLF%" OR UPPER(Producto) LIKE "%HUMEDAS EXT%" THEN Cantidad ELSE 0 END) as rec_imp_auto,
                SUM(CASE WHEN UPPER(Producto) LIKE "BATERIAS ESTACIONARIAS EXT%" THEN Cantidad ELSE 0 END) as rec_imp_ups,
                SUM(CASE WHEN UPPER(Producto) IN ("BATERIAS HUMEDAS NAC", "BATERIAS HUMEDAS MAQUILA") OR UPPER(Producto) = "BATERIAS ESTACIONARIAS NAC" OR UPPER(Producto) LIKE "%GOLF%" OR UPPER(Producto) LIKE "%HUMEDAS EXT%" OR UPPER(Producto) LIKE "BATERIAS ESTACIONARIAS EXT%" THEN Cantidad ELSE 0 END) as total_recepcion,
                SUM(CASE WHEN UPPER(Producto) LIKE "%HUMEDAS%MAQUILA%" OR UPPER(Producto) LIKE "%ESTACIONARIAS%MAQUILA%" THEN Cantidad ELSE 0 END) as maquila_enviada
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

    /**
     * Verifica si las columnas calculadas existen en saldosinsert.
     */
    private function verificarColumnasSaldosinsert(): bool
    {
        $columns = DB::select("SHOW COLUMNS FROM saldosinsert");
        $columnNames = array_column($columns, 'Field');
        return in_array('consumo', $columnNames) && in_array('total_recepcion', $columnNames);
    }

    /**
     * Crea las columnas calculadas faltantes en saldosinsert si no existen.
     */
    private function asegurarColumnasSaldosinsert(): void
    {
        if ($this->verificarColumnasSaldosinsert()) {
            return;
        }

        $columnas = [
            'total_recepcion'               => "decimal(14,2) NOT NULL DEFAULT 0",
            'recepcion_nacional_automotriz'  => "decimal(14,2) NOT NULL DEFAULT 0",
            'recepcion_nacional_ups'         => "decimal(14,2) NOT NULL DEFAULT 0",
            'recepcion_importada_automotriz' => "decimal(14,2) NOT NULL DEFAULT 0",
            'recepcion_importada_ups'        => "decimal(14,2) NOT NULL DEFAULT 0",
            'bateria_nacional_automotriz'    => "decimal(14,2) NOT NULL DEFAULT 0",
            'bateria_nacional_ups'           => "decimal(14,2) NOT NULL DEFAULT 0",
            'bateria_importada_automotriz'   => "decimal(14,2) NOT NULL DEFAULT 0",
            'bateria_importada_ups'          => "decimal(14,2) NOT NULL DEFAULT 0",
            'consumo'                        => "decimal(14,2) NOT NULL DEFAULT 0",
            'maquila_enviada'                => "decimal(14,2) NOT NULL DEFAULT 0",
            'maquila_recibida'               => "decimal(14,2) NOT NULL DEFAULT 0",
            'saldo_cierre'                   => "decimal(14,2) NOT NULL DEFAULT 0",
            'saldo_cierre_automotriz'        => "decimal(14,2) NOT NULL DEFAULT 0",
            'saldo_cierre_ups'              => "decimal(14,2) NOT NULL DEFAULT 0",
        ];

        foreach ($columnas as $columna => $definicion) {
            try {
                DB::statement("ALTER TABLE saldosinsert ADD COLUMN {$columna} {$definicion}");
            } catch (\Exception $e) {
                // Columna ya existe, ignorar
            }
        }
    }
}
