<?php

namespace App\Http\Controllers;

use App\Models\AnalisisCalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CalidadController extends Controller
{
    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        $filtros = $request->only('fecha', 'turno', 'reactor');

        $registros = AnalisisCalidad::activos()
            ->filtrar($filtros)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('calidad.index', compact('registros', 'filtros'));
    }

    /** Formulario de creación. */
    public function create()
    {
        return view('calidad.form', ['analisis' => new AnalisisCalidad(), 'modo' => 'crear']);
    }

    /** Guarda un nuevo registro. */
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['is_deleted']      = 0;
        $data['usernamecalidad'] = $this->username();

        $a = AnalisisCalidad::create($data);

        return redirect()->route('calidad.index')
            ->with('success', "Análisis de calidad #{$a->id} creado correctamente.");
    }

    /** Formulario de edición. */
    public function edit(AnalisisCalidad $calidad)
    {
        abort_if($calidad->is_deleted, 404);
        return view('calidad.form', ['analisis' => $calidad, 'modo' => 'editar']);
    }

    /** Actualiza un registro. */
    public function update(Request $request, AnalisisCalidad $calidad)
    {
        abort_if($calidad->is_deleted, 404);
        $calidad->update($this->validar($request, $calidad->id));

        return redirect()->route('calidad.index')
            ->with('success', "Análisis #{$calidad->id} actualizado.");
    }

    /** Borrado lógico. */
    public function destroy(AnalisisCalidad $calidad)
    {
        $calidad->update(['is_deleted' => 1]);

        return redirect()->route('calidad.index')
            ->with('success', "Análisis #{$calidad->id} eliminado.");
    }

    /**
     * Validación compartida por store/update.
     * Unicidad por fecha + grupo + turno + reactor + hora
     * (permite varias lecturas por turno, pero no dos iguales en el mismo reactor y hora).
     */
    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $unico = Rule::unique('analisiscalidad', 'fecha')
            ->where(fn ($q) => $q
                ->where('grupocalidad', $request->input('grupocalidad'))
                ->where('turnocalidad', $request->input('turnocalidad'))
                ->where('reactor', $request->input('reactor'))
                ->where('hora', $request->input('hora'))
                ->where('is_deleted', 0));
        if ($ignoreId) {
            $unico->ignore($ignoreId);
        }

        $reglas = [
            'fecha'        => ['required', 'date', $unico],
            'hora'         => ['required', 'date_format:H:i'],
            'turnocalidad' => ['required', 'in:' . implode(',', AnalisisCalidad::TURNOS)],
            'reactor'      => ['required', 'in:' . implode(',', AnalisisCalidad::REACTORES)],
            'filtro'       => ['nullable', 'in:' . implode(',', AnalisisCalidad::FILTROS)],
            'grupocalidad' => ['required', 'in:' . implode(',', AnalisisCalidad::GRUPOS)],
        ];
        foreach (array_keys(AnalisisCalidad::MEDICIONES) as $campo) {
            $reglas[$campo] = ['nullable', 'numeric', 'min:0'];
        }

        return $request->validate($reglas, [
            'fecha.unique' => 'Ya existe un análisis para esa fecha, grupo, turno, reactor y hora.',
        ], [
            'turnocalidad' => 'turno',
            'grupocalidad' => 'grupo',
        ]);
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
