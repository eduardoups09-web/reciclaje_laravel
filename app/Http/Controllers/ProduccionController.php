<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProduccionController extends Controller
{
    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        $filtros = $request->only('fecha', 'grupo', 'turno');

        $registros = Salida::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechasalida')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('produccion.index', compact('registros', 'filtros'));
    }

    /** Formulario de creación. */
    public function create()
    {
        return view('produccion.form', ['salida' => new Salida(), 'modo' => 'crear']);
    }

    /** Guarda un nuevo registro. */
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['status_id']          = 1;
        $data['is_deleted']         = 0;
        $data['usernameproduccion'] = $this->username();

        $salida = Salida::create($data);

        return redirect()->route('produccion.index')
            ->with('success', "Registro de producción #{$salida->id} creado correctamente.");
    }

    /** Formulario de edición. */
    public function edit(Salida $produccion)
    {
        abort_if($produccion->is_deleted, 404);
        return view('produccion.form', ['salida' => $produccion, 'modo' => 'editar']);
    }

    /** Actualiza un registro. */
    public function update(Request $request, Salida $produccion)
    {
        abort_if($produccion->is_deleted, 404);
        $produccion->update($this->validar($request, $produccion->id));

        return redirect()->route('produccion.index')
            ->with('success', "Registro #{$produccion->id} actualizado.");
    }

    /** Borrado lógico (is_deleted = 1). */
    public function destroy(Salida $produccion)
    {
        $produccion->update(['is_deleted' => 1]);

        return redirect()->route('produccion.index')
            ->with('success', "Registro #{$produccion->id} eliminado.");
    }

    /**
     * Validación compartida por store/update.
     * Incluye unicidad por fecha + grupo + turno (un registro de producción por turno).
     */
    private function validar(Request $request, ?int $ignoreId = null): array
    {
        // Regla de unicidad: no permitir dos registros activos para el mismo turno.
        $unico = Rule::unique('salidas', 'fechasalida')
            ->where(fn ($q) => $q
                ->where('gruposalida', $request->input('gruposalida'))
                ->where('turnosalida', $request->input('turnosalida'))
                ->where('is_deleted', 0));
        if ($ignoreId) {
            $unico->ignore($ignoreId);
        }

        $reglas = [
            'fechasalida' => ['required', 'date', $unico],
            'gruposalida' => ['required', 'in:' . implode(',', Salida::GRUPOS)],
            'turnosalida' => ['required', 'in:' . implode(',', Salida::TURNOS)],
        ];
        foreach (array_keys(Salida::CAMPOS_NUMERICOS) as $campo) {
            $reglas[$campo] = ['nullable', 'integer', 'min:0'];
        }

        return $request->validate($reglas, [
            'fechasalida.unique' => 'Ya existe un registro de producción para esa fecha, grupo y turno.',
        ], [
            'fechasalida' => 'fecha',
            'gruposalida' => 'grupo',
            'turnosalida' => 'turno',
        ]);
    }

    /** Username del usuario logueado (cae al name si no tiene username). */
    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
