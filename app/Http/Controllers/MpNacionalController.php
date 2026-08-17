<?php

namespace App\Http\Controllers;

use App\Models\MpNacional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MpNacionalController extends Controller
{
    public function index(Request $request)
    {
        $turnoActual = now()->hour >= 7 && now()->hour < 19 ? 'Diurno' : 'Nocturno';

        $filtros = [
            'fecha' => $request->input('fecha') ?? now()->toDateString(),
            'turno' => $request->input('turno') ?? $turnoActual,
        ];

        $registros = MpNacional::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechanacional')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('mpnacional.index', compact('registros', 'filtros'));
    }

    public function create()
    {
        $data = ['mpnacional' => new MpNacional(), 'modo' => 'crear'];

        if (request()->ajax()) {
            return view('mpnacional._form-modal', $data);
        }

        return view('mpnacional.form', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']       = 1;
        $data['is_deleted']      = 0;
        $data['usernamenacional'] = $this->username();

        $m = MpNacional::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "MP Nacional #{$m->id} creado correctamente."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'mpnacional'])
            ->with('success', "MP Nacional #{$m->id} creado correctamente.");
    }

    public function edit(MpNacional $mpnacional)
    {
        abort_if($mpnacional->is_deleted, 404);
        $data = ['mpnacional' => $mpnacional, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('mpnacional._form-modal', $data);
        }

        return view('mpnacional.form', $data);
    }

    public function update(Request $request, MpNacional $mpnacional)
    {
        abort_if($mpnacional->is_deleted, 404);
        $mpnacional->update($this->validar($request));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "MP Nacional #{$mpnacional->id} actualizado."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'mpnacional'])
            ->with('success', "MP Nacional #{$mpnacional->id} actualizado.");
    }

    public function destroy(MpNacional $mpnacional)
    {
        $mpnacional->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'mpnacional'])
            ->with('success', "MP Nacional #{$mpnacional->id} eliminado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fechanacional' => ['required', 'date'],
            'gruponacional' => ['required', 'in:' . implode(',', MpNacional::GRUPOS)],
            'turnonacional' => ['required', 'in:' . implode(',', MpNacional::TURNOS)],
            'bateriatipo'   => ['required', 'in:' . implode(',', MpNacional::TIPOS_BATERIA)],
            'pesobateria'   => ['nullable', 'integer', 'min:0'],
        ], [], [
            'fechanacional' => 'fecha',
            'gruponacional' => 'grupo',
            'turnonacional' => 'turno',
            'bateriatipo'   => 'tipo de batería',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
