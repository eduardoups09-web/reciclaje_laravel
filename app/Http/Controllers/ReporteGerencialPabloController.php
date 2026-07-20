<?php

namespace App\Http\Controllers;

use App\Models\ReporteGerencialPablo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReporteGerencialPabloController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::now();
        $mesAnterior = $hoy->copy()->subMonth();

        $filtros = [
            'anio' => (int) ($request->input('anio') ?? $mesAnterior->year),
        ];

        $registrosGuardados = ReporteGerencialPablo::activos()
            ->where('anio', $filtros['anio'])
            ->get()
            ->keyBy('mes');

        $registros = collect();
        $mesActual = ($filtros['anio'] == $hoy->year) ? $hoy->month : 12;

        for ($m = 1; $m <= $mesActual; $m++) {
            if ($registrosGuardados->has($m)) {
                $r = $registrosGuardados[$m];
                $valores = [
                    'saldo_total'                    => $r->saldo_total,
                    'total_recepcion'                => $r->total_recepcion,
                    'recepcion_nacional_automotriz'  => $r->recepcion_nacional_automotriz,
                    'recepcion_nacional_ups'         => $r->recepcion_nacional_ups,
                    'recepcion_importada_automotriz' => $r->recepcion_importada_automotriz,
                    'recepcion_importada_ups'        => $r->recepcion_importada_ups,
                    'bateria_nacional_automotriz'    => $r->bateria_nacional_automotriz,
                    'bateria_nacional_ups'           => $r->bateria_nacional_ups,
                    'bateria_importada_automotriz'   => $r->bateria_importada_automotriz,
                    'bateria_importada_ups'          => $r->bateria_importada_ups,
                    'consumo'                        => $r->consumo,
                    'maquila_enviada'                => $r->maquila_enviada,
                    'maquila_recibida'               => $r->maquila_recibida,
                    'total_maquila'                  => $r->maquila_enviada + $r->maquila_recibida,
                ];
                $formulas = ReporteGerencialPablo::calcularFormulas($valores);
                $registros->push((object) array_merge([
                    'id'          => $r->id,
                    'mes'         => $m,
                    'anio'        => $filtros['anio'],
                    'guardado'    => true,
                ], $valores, $formulas));
            } else {
                $valores = ReporteGerencialPablo::calcularValores($m, $filtros['anio']);
                $formulas = ReporteGerencialPablo::calcularFormulas($valores);
                $registros->push((object) array_merge([
                    'id'          => null,
                    'mes'         => $m,
                    'anio'        => $filtros['anio'],
                    'guardado'    => false,
                ], $valores, $formulas));
            }
        }

        return view('reportes_gerenciales_pablo.index', compact('registros', 'filtros'));
    }

    public function create(Request $request)
    {
        $hoy = Carbon::now();
        $mesAnterior = $hoy->copy()->subMonth();

        $mes  = (int) $request->input('mes', $mesAnterior->month);
        $anio = (int) $request->input('anio', $mesAnterior->year);

        $existe = ReporteGerencialPablo::activos()
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if ($existe) {
            return redirect()->route('pablo.edit', $existe)
                ->with('success', "Ya existe un reporte para " . ReporteGerencialPablo::MESES[$mes] . " $anio. Se abrió para edición.");
        }

        $valores = ReporteGerencialPablo::calcularValores($mes, $anio);
        $formulas = ReporteGerencialPablo::calcularFormulas($valores);

        return view('reportes_gerenciales_pablo.form', [
            'reporte'  => new ReporteGerencialPablo(),
            'modo'     => 'crear',
            'mes'      => $mes,
            'anio'     => $anio,
            'valores'  => $valores,
            'formulas' => $formulas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        $existe = ReporteGerencialPablo::activos()
            ->where('mes', $data['mes'])
            ->where('anio', $data['anio'])
            ->first();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['mes' => 'Ya existe un reporte para ese mes y año.']);
        }

        ReporteGerencialPablo::create($data);

        return redirect()->route('pablo.index', ['anio' => $data['anio']])
            ->with('success', 'Reporte gerencial creado correctamente.');
    }

    public function edit(ReporteGerencialPablo $reporte)
    {
        abort_if($reporte->is_deleted, 404);

        $formulas = ReporteGerencialPablo::calcularFormulas([
            'saldo_total'                    => $reporte->saldo_total,
            'total_recepcion'                => $reporte->total_recepcion,
            'recepcion_nacional_automotriz'  => $reporte->recepcion_nacional_automotriz,
            'recepcion_nacional_ups'         => $reporte->recepcion_nacional_ups,
            'recepcion_importada_automotriz' => $reporte->recepcion_importada_automotriz,
            'recepcion_importada_ups'        => $reporte->recepcion_importada_ups,
            'bateria_nacional_automotriz'    => $reporte->bateria_nacional_automotriz,
            'bateria_nacional_ups'           => $reporte->bateria_nacional_ups,
            'bateria_importada_automotriz'   => $reporte->bateria_importada_automotriz,
            'bateria_importada_ups'          => $reporte->bateria_importada_ups,
            'consumo'                        => $reporte->consumo,
            'maquila_enviada'                => $reporte->maquila_enviada,
            'maquila_recibida'               => $reporte->maquila_recibida,
        ]);

        return view('reportes_gerenciales_pablo.form', [
            'reporte'  => $reporte,
            'modo'     => 'editar',
            'mes'      => $reporte->mes,
            'anio'     => $reporte->anio,
            'valores'  => [
                'saldo_total'                    => $reporte->saldo_total,
                'total_recepcion'                => $reporte->total_recepcion,
                'recepcion_nacional_automotriz'  => $reporte->recepcion_nacional_automotriz,
                'recepcion_nacional_ups'         => $reporte->recepcion_nacional_ups,
                'recepcion_importada_automotriz' => $reporte->recepcion_importada_automotriz,
                'recepcion_importada_ups'        => $reporte->recepcion_importada_ups,
                'bateria_nacional_automotriz'    => $reporte->bateria_nacional_automotriz,
                'bateria_nacional_ups'           => $reporte->bateria_nacional_ups,
                'bateria_importada_automotriz'   => $reporte->bateria_importada_automotriz,
                'bateria_importada_ups'          => $reporte->bateria_importada_ups,
                'consumo'                        => $reporte->consumo,
                'maquila_enviada'                => $reporte->maquila_enviada,
                'maquila_recibida'               => $reporte->maquila_recibida,
                'total_maquila'                  => $reporte->maquila_enviada + $reporte->maquila_recibida,
                'saldo_cierre_automotriz'        => $reporte->saldo_cierre_automotriz ?? 0,
                'saldo_cierre_ups'               => $reporte->saldo_cierre_ups ?? 0,
            ],
            'formulas' => $formulas,
        ]);
    }

    public function update(Request $request, ReporteGerencialPablo $reporte)
    {
        abort_if($reporte->is_deleted, 404);
        $reporte->update($this->validar($request, $reporte->id));

        return redirect()->route('pablo.index', ['anio' => $reporte->anio])
            ->with('success', "Reporte #{$reporte->id} actualizado.");
    }

    public function destroy(ReporteGerencialPablo $reporte)
    {
        $reporte->update(['is_deleted' => 1]);

        return redirect()->route('pablo.index')
            ->with('success', "Reporte #{$reporte->id} eliminado.");
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $reglas = [
            'mes'                                  => ['required', 'integer', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            'anio'                                 => ['required', 'integer', 'min:2020', 'max:2099'],
            'saldo_total'                          => ['nullable', 'numeric'],
            'total_recepcion'                      => ['nullable', 'numeric'],
            'recepcion_nacional_automotriz'        => ['nullable', 'numeric'],
            'recepcion_nacional_ups'               => ['nullable', 'numeric'],
            'recepcion_importada_automotriz'       => ['nullable', 'numeric'],
            'recepcion_importada_ups'              => ['nullable', 'numeric'],
            'bateria_nacional_automotriz'          => ['nullable', 'numeric'],
            'bateria_nacional_ups'                 => ['nullable', 'numeric'],
            'bateria_importada_automotriz'         => ['nullable', 'numeric'],
            'bateria_importada_ups'                => ['nullable', 'numeric'],
            'consumo'                              => ['nullable', 'numeric'],
            'maquila_enviada'                      => ['nullable', 'numeric'],
            'maquila_recibida'                     => ['nullable', 'numeric'],
        ];

        return $request->validate($reglas, [], [
            'mes'  => 'mes',
            'anio' => 'año',
        ]);
    }
}
