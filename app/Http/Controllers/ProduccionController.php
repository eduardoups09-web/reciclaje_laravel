<?php

namespace App\Http\Controllers;

use App\Models\AnalisisCalidad;
use App\Models\Salida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduccionController extends Controller
{
    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        $filtros = [
            'anio' => $request->input('anio') ?? now()->year,
            'mes'  => $request->input('mes') ?? now()->month,
        ];

        $registros = Salida::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechasalida')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $anios = Salida::selectRaw('YEAR(fechasalida) as anio')
            ->distinct()
            ->whereNotNull('fechasalida')
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('produccion.index', compact('registros', 'filtros', 'anios'));
    }

    /** Formulario de creación. */
    public function create()
    {
        $data = ['salida' => new Salida(), 'modo' => 'crear'];

        if (request()->ajax()) {
            return view('produccion._form-modal', $data);
        }

        return view('produccion.form', $data);
    }

    /** Guarda un nuevo registro. */
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data = $this->aplicarFactores($request, $data);
        $data['status_id']          = 1;
        $data['is_deleted']         = 0;
        $data['usernameproduccion'] = $this->username();

        $salida = Salida::create($data);

        $descargas = $data['descargas'] ?? 0;
        if ($descargas > 0) {
            for ($i = 0; $i < $descargas; $i++) {
                AnalisisCalidad::create([
                    'fecha'           => $data['fechasalida'],
                    'turnocalidad'    => $data['turnosalida'],
                    'grupocalidad'    => $data['gruposalida'],
                    'is_deleted'      => 0,
                    'usernamecalidad' => $this->username(),
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Registro de producción #{$salida->id} creado correctamente." . ($descargas > 0 ? " Se generaron {$descargas} análisis de calidad." : '')]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'produccion'])
            ->with('success', "Registro de producción #{$salida->id} creado correctamente.");
    }

    /** Formulario de edición. */
    public function edit(Salida $produccion)
    {
        abort_if($produccion->is_deleted, 404);
        $data = ['salida' => $produccion, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('produccion._form-modal', $data);
        }

        return view('produccion.form', $data);
    }

    /** Actualiza un registro. */
    public function update(Request $request, Salida $produccion)
    {
        abort_if($produccion->is_deleted, 404);
        $data = $this->validar($request, $produccion->id);
        $data = $this->aplicarFactores($request, $data);
        $produccion->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Registro #{$produccion->id} actualizado."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'produccion'])
            ->with('success', "Registro #{$produccion->id} actualizado.");
    }

    /** Borrado lógico (is_deleted = 1). */
    public function destroy(Salida $produccion)
    {
        $produccion->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'produccion'])
            ->with('success', "Registro #{$produccion->id} eliminado.");
    }

    /**
     * Validación compartida por store/update.
     * Incluye unicidad por fecha + grupo + turno (un registro de producción por turno).
     */
    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $reglas = [
            'fechasalida' => ['required', 'date'],
            'gruposalida' => ['required', 'in:' . implode(',', Salida::GRUPOS)],
            'turnosalida' => ['required', 'in:' . implode(',', Salida::TURNOS)],
        ];
        foreach (array_keys(Salida::CAMPOS_NUMERICOS) as $campo) {
            $reglas[$campo] = ['nullable', 'integer', 'min:0'];
        }
        foreach (Salida::CAMPOS_CON_FACTOR as $campo) {
            $reglas["factor_{$campo}"] = ['nullable', 'numeric', 'in:' . implode(',', Salida::FACTORES)];
        }

        return $request->validate($reglas, [], [
            'fechasalida' => 'fecha',
            'gruposalida' => 'grupo',
            'turnosalida' => 'turno',
        ]);
    }

    /**
     * Aplica el factor de rendimiento a los 5 campos: campo * factor.
     * El resultado redondeado se guarda en la BD.
     */
    private function aplicarFactores(Request $request, array $data): array
    {
        foreach (Salida::CAMPOS_CON_FACTOR as $campo) {
            $valor = $request->input($campo);
            $factor = $request->input("factor_{$campo}", 0.97);
            if (!is_null($valor) && $valor !== '' && $factor) {
                $data[$campo] = round($valor * $factor);
            }
        }
        return $data;
    }

    /** Username del usuario logueado (cae al name si no tiene username). */
    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
