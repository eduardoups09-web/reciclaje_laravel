<?php

namespace App\Http\Controllers;

use App\Models\ReporteGerencial;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReporteGerencialController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::now();
        $mesAnterior = $hoy->copy()->subMonth();

        $filtros = [
            'anio' => (int) ($request->input('anio') ?? $mesAnterior->year),
        ];

        // Obtener registros guardados para el año seleccionado
        $registrosGuardados = ReporteGerencial::activos()
            ->where('anio', $filtros['anio'])
            ->get()
            ->keyBy('mes');

        // Construir array de meses hasta el mes actual
        $registros = collect();
        $mesActual = ($filtros['anio'] == $hoy->year) ? $hoy->month : 12;

        for ($m = 1; $m <= $mesActual; $m++) {
            if ($registrosGuardados->has($m)) {
                // Registro guardado en BD
                $r = $registrosGuardados[$m];
                $valores = [
                    'saldo_total'      => $r->saldo_total,
                    'total_recepcion'  => $r->total_recepcion,
                    'consumo'          => $r->consumo,
                    'maquila_enviada'  => $r->maquila_enviada,
                    'maquila_recibida' => $r->maquila_recibida,
                ];
                $formulas = ReporteGerencial::calcularFormulas($valores);
                $registros->push((object) array_merge([
                    'id'          => $r->id,
                    'mes'         => $m,
                    'anio'        => $filtros['anio'],
                    'guardado'    => true,
                ], $valores, $formulas));
            } else {
                // Calcular valores desde tablas origen
                $valores = ReporteGerencial::calcularValores($m, $filtros['anio']);
                $formulas = ReporteGerencial::calcularFormulas($valores);
                $registros->push((object) array_merge([
                    'id'          => null,
                    'mes'         => $m,
                    'anio'        => $filtros['anio'],
                    'guardado'    => false,
                ], $valores, $formulas));
            }
        }

        return view('reportes_gerenciales.index', compact('registros', 'filtros'));
    }

    public function create(Request $request)
    {
        $hoy = Carbon::now();
        $mesAnterior = $hoy->copy()->subMonth();

        $mes  = (int) $request->input('mes', $mesAnterior->month);
        $anio = (int) $request->input('anio', $mesAnterior->year);

        // Verificar si ya existe un registro para ese mes/año
        $existe = ReporteGerencial::activos()
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if ($existe) {
            return redirect()->route('reportes-gerenciales.edit', $existe)
                ->with('success', "Ya existe un reporte para " . ReporteGerencial::MESES[$mes] . " $anio. Se abrió para edición.");
        }

        $valores = ReporteGerencial::calcularValores($mes, $anio);
        $formulas = ReporteGerencial::calcularFormulas($valores);

        return view('reportes_gerenciales.form', [
            'reporte'  => new ReporteGerencial(),
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

        // Verificar unicidad
        $existe = ReporteGerencial::activos()
            ->where('mes', $data['mes'])
            ->where('anio', $data['anio'])
            ->first();

        if ($existe) {
            return back()->withInput()
                ->withErrors(['mes' => 'Ya existe un reporte para ese mes y año.']);
        }

        ReporteGerencial::create($data);

        return redirect()->route('reportes-gerenciales.index', ['anio' => $data['anio']])
            ->with('success', 'Reporte gerencial creado correctamente.');
    }

    public function edit(ReporteGerencial $reporte)
    {
        abort_if($reporte->is_deleted, 404);

        $formulas = ReporteGerencial::calcularFormulas([
            'saldo_total'      => $reporte->saldo_total,
            'total_recepcion'  => $reporte->total_recepcion,
            'consumo'          => $reporte->consumo,
            'maquila_enviada'  => $reporte->maquila_enviada,
            'maquila_recibida' => $reporte->maquila_recibida,
        ]);

        return view('reportes_gerenciales.form', [
            'reporte'  => $reporte,
            'modo'     => 'editar',
            'mes'      => $reporte->mes,
            'anio'     => $reporte->anio,
            'valores'  => [
                'saldo_total'      => $reporte->saldo_total,
                'total_recepcion'  => $reporte->total_recepcion,
                'consumo'          => $reporte->consumo,
                'maquila_enviada'  => $reporte->maquila_enviada,
                'maquila_recibida' => $reporte->maquila_recibida,
            ],
            'formulas' => $formulas,
        ]);
    }

    public function update(Request $request, ReporteGerencial $reporte)
    {
        abort_if($reporte->is_deleted, 404);
        $reporte->update($this->validar($request, $reporte->id));

        return redirect()->route('reportes-gerenciales.index', ['anio' => $reporte->anio])
            ->with('success', "Reporte #{$reporte->id} actualizado.");
    }

    public function destroy(ReporteGerencial $reporte)
    {
        $reporte->update(['is_deleted' => 1]);

        return redirect()->route('reportes-gerenciales.index')
            ->with('success', "Reporte #{$reporte->id} eliminado.");
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $reglas = [
            'mes'              => ['required', 'integer', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            'anio'             => ['required', 'integer', 'min:2020', 'max:2099'],
            'saldo_total'      => ['nullable', 'numeric'],
            'total_recepcion'  => ['nullable', 'numeric'],
            'consumo'          => ['nullable', 'numeric'],
            'maquila_enviada'  => ['nullable', 'numeric'],
            'maquila_recibida' => ['nullable', 'numeric'],
        ];

        return $request->validate($reglas, [], [
            'mes'  => 'mes',
            'anio' => 'año',
        ]);
    }
}
