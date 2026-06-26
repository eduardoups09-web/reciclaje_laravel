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
        $filtros = [
            'anio' => $request->input('anio') ?? now()->year,
            'mes'  => $request->input('mes')  ?? now()->month,
        ];

        $registros = AnalisisCalidad::activos()
            ->filtrar($filtros)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $anios = AnalisisCalidad::activos()
            ->whereYear('fecha', '>', 2000)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('calidad.index', compact('registros', 'filtros', 'anios'));
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

        return redirect()->route('operaciones.index', ['tab' => 'calidad'])
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
        $data = $this->validar($request, $calidad->id);
        $calidad->update($data);

        return redirect()->route('operaciones.index', ['tab' => 'calidad'])
            ->with('success', "Análisis #{$calidad->id} actualizado.");
    }

    /** Borrado lógico. */
    public function destroy(AnalisisCalidad $calidad)
    {
        $calidad->update(['is_deleted' => 1]);

        return redirect()->route('operaciones.index', ['tab' => 'calidad'])
            ->with('success', "Análisis #{$calidad->id} eliminado.");
    }

    /**
     * Validación compartida por store/update.
     */
    private function validar(Request $request, ?int $ignoreId = null): array
    {
        $reglas = [
            'fecha'        => ['required', 'date'],
            'hora'         => ['required', 'date_format:H:i'],
            'turnocalidad' => ['required', 'in:' . implode(',', AnalisisCalidad::TURNOS)],
            'reactor1'     => ['nullable', 'boolean'],
            'reactor2'     => ['nullable', 'boolean'],
            'reactor3'     => ['nullable', 'boolean'],
            'reactor4'     => ['nullable', 'boolean'],
            'filtro'       => ['nullable', 'in:' . implode(',', AnalisisCalidad::FILTROS)],
            'grupocalidad' => ['required', 'in:' . implode(',', AnalisisCalidad::GRUPOS)],
            'humedad'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pi'           => ['nullable', 'numeric', 'min:0'],
            'pf'           => ['nullable', 'numeric', 'min:0'],
        ];
        foreach (array_keys(AnalisisCalidad::MEDICIONES) as $campo) {
            if ($campo === 'humedad') continue;
            $reglas[$campo] = ['nullable', 'numeric', 'min:0'];
        }

        return $request->validate($reglas, [], [
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
