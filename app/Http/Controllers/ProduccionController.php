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

        $totales = $this->calcularTotales($filtros);

        return view('produccion.index', compact('registros', 'filtros', 'anios', 'totales'));
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
        $data = $this->guardarFactores($request, $data);
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
        $data = $this->guardarFactores($request, $data);
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
     * Guarda el factor de rendimiento en la columna calculable correspondiente.
     * El valor crudo se mantiene intacto en su campo original.
     * También asigna el nombre del producto en los campos varchar.
     */
    private function guardarFactores(Request $request, array $data): array
    {
        foreach (Salida::CAMPOS_FACTOR_MAP as $campo => $columna) {
            $factor = $request->input("factor_{$campo}");
            $data[$columna] = $factor ?? 0.97;
        }

        $nombres = [
            'polipropilenokg' => ['polipropileno', 'Polipropileno'],
            'abskg'           => ['abs', 'ABS'],
            'separadorkg'     => ['separador', 'Separador'],
        ];
        foreach ($nombres as $campoKg => [$campoVarchar, $nombre]) {
            $data[$campoVarchar] = (!empty($data[$campoKg]) && $data[$campoKg] > 0) ? $nombre : null;
        }

        return $data;
    }

    /**
     * Calcula los totales de la página actual: suma de valores crudos, factor promedio, y total calculado.
     */
    private function calcularTotales(array $filtros): array
    {
        $totales = [];
        $granSuma = 0;
        $granCalculado = 0;

        foreach (Salida::CAMPOS_FACTOR_MAP as $campo => $columna) {
            $resultado = Salida::activos()
                ->filtrar($filtros)
                ->selectRaw("SUM(COALESCE({$campo}, 0)) as suma, AVG(COALESCE({$columna}, 0.97)) as factor, SUM(COALESCE({$campo}, 0) * COALESCE({$columna}, 0.97)) as calculado")
                ->first();

            $suma = (float) ($resultado->suma ?? 0);
            $factor = (float) ($resultado->factor ?? 0.97);
            $calculado = round((float) ($resultado->calculado ?? 0));

            $totales[$campo] = compact('suma', 'factor', 'calculado');
            $granSuma += $suma;
            $granCalculado += $calculado;
        }

        $totales['gran_total'] = [
            'suma' => $granSuma,
            'calculado' => $granCalculado,
        ];

        return $totales;
    }

    /** Username del usuario logueado (cae al name si no tiene username). */
    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }

}
