<?php

namespace App\Http\Controllers;

use App\Models\MpImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MpImportController extends Controller
{
    public function index(Request $request)
    {
        $turnoActual = now()->hour >= 7 && now()->hour < 19 ? 'Diurno' : 'Nocturno';

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?? $turnoActual,
        ];

        $registros = MpImport::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechaimport')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('mpimport.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        $data = ['mpimport' => new MpImport(), 'modo' => 'crear'];

        if (request()->ajax()) {
            return view('mpimport._form-modal', $data);
        }

        return view('mpimport.form', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']     = 1;
        $data['is_deleted']    = 0;
        $data['usernameimport'] = $this->username();

        $m = MpImport::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "MP Importación #{$m->id} creado correctamente."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'mpimport'])
            ->with('success', "MP Importación #{$m->id} creado correctamente.");
    }

    public function edit(MpImport $mpimport)
    {
        abort_if($mpimport->is_deleted, 404);
        $data = ['mpimport' => $mpimport, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('mpimport._form-modal', $data);
        }

        return view('mpimport.form', $data);
    }

    public function update(Request $request, MpImport $mpimport)
    {
        abort_if($mpimport->is_deleted, 404);
        $mpimport->update($this->validar($request));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "MP Importación #{$mpimport->id} actualizado."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'mpimport'])
            ->with('success', "MP Importación #{$mpimport->id} actualizado.");
    }

    public function destroy(MpImport $mpimport)
    {
        $mpimport->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'mpimport'])
            ->with('success', "MP Importación #{$mpimport->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fechaimport'      => ['required', 'date'],
            'grupoimport'      => ['required', 'in:' . implode(',', MpImport::GRUPOS)],
            'turnoimport'      => ['required', 'in:' . implode(',', MpImport::TURNOS)],
            'bateriatipoimport' => ['required', 'in:' . implode(',', MpImport::TIPOS_BATERIA)],
            'pesobateriaimport' => ['nullable', 'integer', 'min:0'],
            'metalicoimport'   => ['nullable', 'integer', 'min:0'],
            'pastaimport'      => ['nullable', 'integer', 'min:0'],
            'placasimport'     => ['nullable', 'integer', 'min:0'],
        ], [], [
            'fechaimport'       => 'fecha',
            'grupoimport'       => 'grupo',
            'turnoimport'       => 'turno',
            'bateriatipoimport' => 'tipo de batería',
            'pesobateriaimport' => 'peso batería',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
