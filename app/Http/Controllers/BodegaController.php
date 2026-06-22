<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BodegaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only('fecha', 'tipo', 'buscar', 'grupo', 'turno');

        $registros = Bodega::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechainicio')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('bodega.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        return view('bodega.form', ['bodega' => new Bodega(), 'modo' => 'crear']);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['is_deleted']     = 0;
        $data['usernameBodega'] = $this->username();

        $b = Bodega::create($data);

        return redirect()->route('bodega.index')
            ->with('success', "Movimiento de bodega #{$b->id} creado correctamente.");
    }

    public function edit(Bodega $bodega)
    {
        abort_if($bodega->is_deleted, 404);
        return view('bodega.form', ['bodega' => $bodega, 'modo' => 'editar']);
    }

    public function update(Request $request, Bodega $bodega)
    {
        abort_if($bodega->is_deleted, 404);
        $bodega->update($this->validar($request));

        return redirect()->route('bodega.index')
            ->with('success', "Movimiento #{$bodega->id} actualizado.");
    }

    public function destroy(Bodega $bodega)
    {
        $bodega->update(['is_deleted' => 1]);

        return redirect()->route('bodega.index')
            ->with('success', "Movimiento #{$bodega->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fechainicio'         => ['required', 'date'],
            'grupo'               => ['required', 'in:' . implode(',', Bodega::GRUPOS)],
            'turno'               => ['required', 'in:' . implode(',', Bodega::TURNOS)],
            'tipobateria'         => ['nullable', 'string', 'max:50'],
            'contenedor'          => ['nullable', 'string', 'max:50'],
            'cantidad'            => ['required', 'numeric'],
            'unidad'              => ['nullable', 'string', 'max:100'],
            'consecutivo'         => ['nullable', 'integer'],
            'despacho'            => ['nullable', 'string', 'max:50'],
            'nombreDestinatario'  => ['nullable', 'string', 'max:100'],
            'rucDestinatario'     => ['nullable', 'string', 'max:255'],
            'nombreTransportista' => ['nullable', 'string', 'max:255'],
            'transportistaRuc'    => ['nullable', 'string', 'max:100'],
            'placaTransportista'  => ['nullable', 'string', 'max:100'],
            'observacion'         => ['nullable', 'string', 'max:100'],
        ], [], [
            'fechainicio' => 'fecha',
            'cantidad'    => 'cantidad',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
