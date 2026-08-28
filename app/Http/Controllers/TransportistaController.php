<?php

namespace App\Http\Controllers;

use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportistaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'transportistas' => ['required', 'string', 'max:255'],
            'ruc'            => ['nullable', 'string', 'max:100'],
            'placa'          => ['nullable', 'string', 'max:100'],
        ]);

        $data['is_deleted']             = 0;
        $data['fecha']                  = now()->toDateString();
        $data['usernameTransportista']  = $this->username();

        $t = Transportista::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Transportista registrado correctamente.',
                'transportista' => [
                    'nombre' => $t->transportistas,
                    'ruc'    => $t->ruc,
                    'placa'  => $t->placa,
                ],
            ]);
        }

        return redirect()->back()
            ->with('success', 'Transportista registrado correctamente.');
    }

    public function obtener(Request $request)
    {
        $nombre = $request->query('nombre');

        if (!$nombre) {
            return response()->json(['success' => false]);
        }

        $t = Transportista::activos()
            ->where('transportistas', $nombre)
            ->first();

        if (!$t) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success' => true,
            'ruc'     => $t->ruc,
            'placa'   => $t->placa,
        ]);
    }

    public function listar(Request $request)
    {
        $termino = $request->query('q');

        $query = Transportista::activos()->orderBy('transportistas');

        if ($termino) {
            $query->where('transportistas', 'like', "%{$termino}%");
        }

        $items = $query->get(['id', 'transportistas', 'ruc', 'placa']);

        return response()->json([
            'success' => true,
            'items'   => $items,
        ]);
    }

    public function update(Request $request, Transportista $transportista)
    {
        $data = $request->validate([
            'transportistas' => ['required', 'string', 'max:255'],
            'ruc'            => ['nullable', 'string', 'max:100'],
            'placa'          => ['nullable', 'string', 'max:100'],
        ]);

        $transportista->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Transportista actualizado correctamente.',
        ]);
    }

    public function destroy(Transportista $transportista)
    {
        $transportista->update(['is_deleted' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Transportista eliminado correctamente.',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u ? ($u->username ?: $u->name) : 'sistema';
    }
}
