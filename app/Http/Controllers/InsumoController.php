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
        return view('insumos.form', ['insumo' => new Insumo(), 'modo' => 'crear']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']     = 1;
        $data['is_deleted']    = 0;
        $data['usernameinsumo'] = $this->username();

        $i = Insumo::create($data);

        return redirect()->route('operaciones.index', ['tab' => 'insumos'])
            ->with('success', "Insumo #{$i->id} creado correctamente.");
    }

    public function edit(Insumo $insumo)
    {
        abort_if($insumo->is_deleted, 404);
        return view('insumos.form', ['insumo' => $insumo, 'modo' => 'editar']);
    }

    public function update(Request $request, Insumo $insumo)
    {
        abort_if($insumo->is_deleted, 404);
        $insumo->update($this->validar($request));

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
