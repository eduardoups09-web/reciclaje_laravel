<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $unidades = DB::table('unidad')->where('is_deleted', 0)->pluck('unidades');
        $transportistas = DB::table('transportista')->where('is_deleted', 0)->pluck('transportistas');
        $motivos = DB::table('motivo')->where('is_deleted', 0)->pluck('motivos');

        return view('bodega.index', compact('registros', 'filtros', 'unidades', 'transportistas', 'motivos'));
    }

    public function create()
    {
        $unidades = DB::table('unidad')->where('is_deleted', 0)->pluck('unidades');
        $transportistas = DB::table('transportista')->where('is_deleted', 0)->pluck('transportistas');
        $motivos = DB::table('motivo')->where('is_deleted', 0)->pluck('motivos');
        $data = ['bodega' => new Bodega(), 'modo' => 'crear', 'unidades' => $unidades, 'transportistas' => $transportistas, 'motivos' => $motivos];

        if (request()->ajax()) {
            return view('bodega._form-modal', $data);
        }

        return view('bodega.form', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['is_deleted']     = 0;
        $data['usernameBodega'] = $this->username();

        $b = Bodega::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Movimiento de bodega #{$b->id} creado correctamente."]);
        }

        return redirect()->route('bodega.index')
            ->with('success', "Movimiento de bodega #{$b->id} creado correctamente.");
    }

    public function edit(Bodega $bodega)
    {
        abort_if($bodega->is_deleted, 404);
        $unidades = DB::table('unidad')->where('is_deleted', 0)->pluck('unidades');
        $transportistas = DB::table('transportista')->where('is_deleted', 0)->pluck('transportistas');
        $motivos = DB::table('motivo')->where('is_deleted', 0)->pluck('motivos');
        $data = ['bodega' => $bodega, 'modo' => 'editar', 'unidades' => $unidades, 'transportistas' => $transportistas, 'motivos' => $motivos];

        if (request()->ajax()) {
            return view('bodega._form-modal', $data);
        }

        return view('bodega.form', $data);
    }

    public function update(Request $request, Bodega $bodega)
    {
        abort_if($bodega->is_deleted, 404);
        $bodega->update($this->validar($request));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Movimiento #{$bodega->id} actualizado."]);
        }

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
            'motivo'              => ['nullable', 'string', 'max:100'],
            'partida'             => ['nullable', 'string', 'max:100'],
            'fechaentrega'        => ['nullable', 'date'],
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
