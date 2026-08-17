<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    /**
     * Listado consolidado de turnos con todas las columnas de datos.
     * Consulta la vista `view_consultamovimientos`.
     */
    public function index(Request $request)
    {
        $filtros = [
            'anio' => $request->input('anio') ?? now()->year,
            'mes'  => $request->input('mes')  ?? now()->month,
        ];

        $registros = Movimiento::query()
            ->when($filtros['anio'] ?? null, fn ($q, $v) => $q->whereYear('fecha', $v))
            ->when($filtros['mes'] ?? null, fn ($q, $v) => $q->whereMonth('fecha', $v))
            ->orderByDesc('fecha')
            ->orderBy('grupo')
            ->orderBy('turno')
            ->get();

        $anios = Movimiento::selectRaw('DISTINCT YEAR(fecha) as anio')
            ->whereYear('fecha', '>', 2000)
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('movimientos.index', compact('registros', 'filtros', 'anios'));
    }

    /**
     * Detalle consolidado de un turno: consulta la vista filtrada por
     * fecha + grupo + turno (rápido, ~1s, porque empuja el filtro a la vista).
     */
    public function show(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'grupo' => ['required', 'string'],
            'turno' => ['required', 'string'],
        ]);

        $m = Movimiento::where('fecha', $data['fecha'])
            ->where('grupo', $data['grupo'])
            ->where('turno', $data['turno'])
            ->first();

        abort_if(!$m, 404, 'No hay datos consolidados para ese turno.');

        return view('movimientos.show', ['m' => $m]);
    }
}
