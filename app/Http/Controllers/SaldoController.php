<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only('fecha', 'grupo', 'turno');

        $registros = Saldo::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechasaldo')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('saldos.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        return view('saldos.form', ['saldo' => new Saldo(), 'modo' => 'crear']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']  = 1;
        $data['is_deleted'] = 0;
        $data['created_at'] = Carbon::today()->toDateString(); // columna DATE

        $s = Saldo::create($data);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$s->id} creado correctamente.");
    }

    public function edit(Saldo $saldo)
    {
        abort_if($saldo->is_deleted, 404);
        return view('saldos.form', ['saldo' => $saldo, 'modo' => 'editar']);
    }

    public function update(Request $request, Saldo $saldo)
    {
        abort_if($saldo->is_deleted, 404);
        $data = $this->validar($request);
        $data['updated_at'] = Carbon::now();
        $saldo->update($data);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$saldo->id} actualizado.");
    }

    public function destroy(Saldo $saldo)
    {
        $saldo->update(['is_deleted' => 1]);

        return redirect()->route('saldos.index')
            ->with('success', "Saldo #{$saldo->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        $reglas = [
            'fechasaldo' => ['required', 'date'],
            'turnosaldo' => ['required', 'in:' . implode(',', Saldo::TURNOS)],
            'gruposaldo' => ['required', 'in:' . implode(',', Saldo::GRUPOS)],
        ];
        // Las cantidades pueden ser negativas (ajustes de inventario).
        foreach (array_keys(Saldo::CANTIDADES) as $campo) {
            $reglas[$campo] = ['nullable', 'integer'];
        }

        return $request->validate($reglas, [], [
            'fechasaldo' => 'fecha',
            'turnosaldo' => 'turno',
            'gruposaldo' => 'grupo',
        ]);
    }
}
