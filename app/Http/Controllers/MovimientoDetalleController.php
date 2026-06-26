<?php

namespace App\Http\Controllers;

use App\Models\MovimientoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovimientoDetalleController extends Controller
{
    public function index(Request $request)
    {
        $turnoActual = now()->hour >= 7 && now()->hour < 19 ? 'Diurno' : 'Nocturno';

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?? $turnoActual,
        ];

        $registros = MovimientoDetalle::activos()
            ->filtrar($filtros)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('movimientodetalle.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        return view('movimientodetalle.form', ['movimientoDetalle' => new MovimientoDetalle(), 'modo' => 'crear']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']  = 1;
        $data['is_deleted'] = 0;

        $m = MovimientoDetalle::create($data);

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', "Movimiento detalle #{$m->id} creado correctamente.");
    }

    public function edit(MovimientoDetalle $movimientoDetalle)
    {
        abort_if($movimientoDetalle->is_deleted, 404);
        return view('movimientodetalle.form', ['movimientoDetalle' => $movimientoDetalle, 'modo' => 'editar']);
    }

    public function update(Request $request, MovimientoDetalle $movimientoDetalle)
    {
        abort_if($movimientoDetalle->is_deleted, 404);
        $movimientoDetalle->update($this->validar($request));

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', "Movimiento detalle #{$movimientoDetalle->id} actualizado.");
    }

    public function destroy(MovimientoDetalle $movimientoDetalle)
    {
        $movimientoDetalle->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', "Movimiento detalle #{$movimientoDetalle->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha' => ['required', 'date'],
            'grupo' => ['required', 'in:' . implode(',', MovimientoDetalle::GRUPOS)],
            'turno' => ['required', 'in:' . implode(',', MovimientoDetalle::TURNOS)],
        ], [], [
            'grupo' => 'grupo',
            'turno' => 'turno',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
