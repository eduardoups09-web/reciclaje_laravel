<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Listado de turnos (fecha + grupo + turno).
     * Se arma desde `movimientodetalle` (barato, ~0.1s) en vez de la vista
     * consolidada, que es muy costosa de recorrer completa (~80s).
     */
    public function index(Request $request)
    {
        $filtros = [
            'anio' => $request->input('anio') ?? now()->year,
            'mes' => $request->input('mes') ?? now()->month,
        ];

        $registros = DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->when($filtros['anio'] ?? null, fn ($q, $v) => $q->whereYear('fecha', $v))
            ->when($filtros['mes'] ?? null, fn ($q, $v) => $q->whereMonth('fecha', $v))
            ->select('fecha', 'grupo', 'turno', DB::raw('MAX(status_id) as status_id'))
            ->groupBy('fecha', 'grupo', 'turno')
            ->orderByDesc('fecha')
            ->orderBy('grupo')
            ->orderBy('turno')
            ->paginate(25)
            ->withQueryString();

        // Años disponibles (descartando fechas inválidas tipo 0000).
        $anios = DB::table('movimientodetalle')
            ->where('is_deleted', 0)
            ->whereYear('fecha', '>', 2000)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
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
