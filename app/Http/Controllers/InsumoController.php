<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $turnoActual = now()->hour >= 7 && now()->hour < 19 ? 'Diurno' : 'Nocturno';

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?? $turnoActual,
        ];

        $registros = Insumo::activos()
            ->filtrar($filtros)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('insumos.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        $data = ['insumo' => new Insumo(), 'modo' => 'crear'];

        if (request()->ajax()) {
            return view('insumos._form-modal', $data);
        }

        return view('insumos.form', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']     = 1;
        $data['is_deleted']    = 0;
        $data['usernameinsumo'] = $this->username();

        $i = Insumo::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Insumo #{$i->id} creado correctamente."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'insumos'])
            ->with('success', "Insumo #{$i->id} creado correctamente.");
    }

    public function edit(Insumo $insumo)
    {
        abort_if($insumo->is_deleted, 404);
        $data = ['insumo' => $insumo, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('insumos._form-modal', $data);
        }

        return view('insumos.form', $data);
    }

    public function update(Request $request, Insumo $insumo)
    {
        abort_if($insumo->is_deleted, 404);
        $insumo->update($this->validar($request));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Insumo #{$insumo->id} actualizado."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'insumos'])
            ->with('success', "Insumo #{$insumo->id} actualizado.");
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'insumos'])
            ->with('success', "Insumo #{$insumo->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha'        => ['required', 'date'],
            'grupoinsumo'  => ['required', 'in:' . implode(',', Insumo::GRUPOS)],
            'turnoinsumo'  => ['required', 'in:' . implode(',', Insumo::TURNOS)],
            'carbonatoSodio' => ['nullable', 'integer', 'min:0'],
            'cal'          => ['nullable', 'integer', 'min:0'],
        ], [], [
            'grupoinsumo' => 'grupo',
            'turnoinsumo' => 'turno',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
