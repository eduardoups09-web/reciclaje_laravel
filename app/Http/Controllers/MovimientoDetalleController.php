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
        $fecha = request('fecha', now()->toDateString());
        $grupo = request('grupo', '1');

        $turnosExistentes = MovimientoDetalle::activos()
            ->where('fecha', $fecha)
            ->where('grupo', $grupo)
            ->pluck('turno')
            ->toArray();

        $data = [
            'movimientoDetalle' => new MovimientoDetalle(),
            'modo' => 'crear',
            'turnosExistentes' => $turnosExistentes,
        ];

        if (request()->ajax()) {
            return view('movimientodetalle._form-modal', $data);
        }

        return view('movimientodetalle.form', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['is_deleted'] = 0;

        $creados = [];
        $turnosMap = ['activar_diurno' => ['turno' => 'Diurno', 'campo_grupo' => 'grupo_diurno'],
                       'activar_nocturno' => ['turno' => 'Nocturno', 'campo_grupo' => 'grupo_nocturno']];

        foreach ($turnosMap as $key => $info) {
            if ($request->filled($key)) {
                $creados[] = MovimientoDetalle::create([
                    'fecha'     => $data['fecha'],
                    'grupo'     => $request->input($info['campo_grupo']),
                    'turno'     => $info['turno'],
                    'status_id' => $data['status_id'],
                    'is_deleted' => 0,
                ]);
            }
        }

        $cantidad = count($creados);
        $mensaje = $cantidad === 1
            ? "Movimiento detalle #{$creados[0]->id} creado correctamente."
            : "{$cantidad} movimientos detalle creados correctamente.";

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', $mensaje);
    }

    public function edit(MovimientoDetalle $movimientoDetalle)
    {
        abort_if($movimientoDetalle->is_deleted, 404);
        $data = ['movimientoDetalle' => $movimientoDetalle, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('movimientodetalle._form-modal', $data);
        }

        return view('movimientodetalle.form', $data);
    }

    public function update(Request $request, MovimientoDetalle $movimientoDetalle)
    {
        abort_if($movimientoDetalle->is_deleted, 404);
        $movimientoDetalle->update($this->validar($request));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Movimiento detalle #{$movimientoDetalle->id} actualizado."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', "Movimiento detalle #{$movimientoDetalle->id} actualizado.");
    }

    public function destroy(MovimientoDetalle $movimientoDetalle)
    {
        $movimientoDetalle->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'movdetalle'])
            ->with('success', "Movimiento detalle #{$movimientoDetalle->id} eliminado.");
    }

    public function updateEstado(Request $request)
    {
        $request->validate([
            'fecha'     => ['required', 'date'],
            'turno'     => ['required', 'in:' . implode(',', MovimientoDetalle::TURNOS)],
            'status_id' => ['required', 'in:1,2,4'],
        ]);

        MovimientoDetalle::activos()
            ->where('fecha', $request->fecha)
            ->where('turno', $request->turno)
            ->update(['status_id' => $request->status_id]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
    }

    public function obtenerEstado(Request $request)
    {
        $request->validate([
            'fecha' => ['required', 'date'],
            'turno' => ['required', 'in:' . implode(',', MovimientoDetalle::TURNOS)],
            'grupo' => ['required', 'in:' . implode(',', MovimientoDetalle::GRUPOS)],
        ]);

        $registro = MovimientoDetalle::obtenerEstadoActual(
            $request->fecha, $request->turno, $request->grupo
        );

        return response()->json([
            'status_id'   => $registro ? $registro->status_id : null,
            'has_records' => $registro !== null,
        ]);
    }

    private function validar(Request $request): array
    {
        $esCrear = $request->isMethod('post');
        $grupos = implode(',', MovimientoDetalle::GRUPOS);

        if ($esCrear) {
            $rules = [
                'fecha'             => ['required', 'date'],
                'status_id'         => ['required', 'in:1,2,4'],
                'activar_diurno'    => ['nullable'],
                'activar_nocturno'  => ['nullable'],
                'grupo_diurno'      => ['nullable', "in:{$grupos}"],
                'grupo_nocturno'    => ['nullable', "in:{$grupos}"],
            ];

            $request->validate([
                'activar_diurno'   => ['required_without:activar_nocturno', 'nullable'],
                'activar_nocturno' => ['required_without:activar_diurno', 'nullable'],
            ], [
                'activar_diurno.required_without' => 'Debe seleccionar al menos un turno.',
                'activar_nocturno.required_without' => 'Debe seleccionar al menos un turno.',
            ]);

            if ($request->filled('activar_diurno')) {
                $rules['grupo_diurno'] = ['required', "in:{$grupos}"];
            }
            if ($request->filled('activar_nocturno')) {
                $rules['grupo_nocturno'] = ['required', "in:{$grupos}"];
            }
        } else {
            $rules = [
                'fecha'     => ['required', 'date'],
                'grupo'     => ['required', "in:{$grupos}"],
                'turno'     => ['required', 'in:' . implode(',', MovimientoDetalle::TURNOS)],
                'status_id' => ['required', 'in:1,2,4'],
            ];
        }

        return $request->validate($rules, [], [
            'grupo'           => 'grupo',
            'grupo_diurno'    => 'grupo diurno',
            'grupo_nocturno'  => 'grupo nocturno',
            'turno'           => 'turno',
            'status_id'       => 'estado',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
