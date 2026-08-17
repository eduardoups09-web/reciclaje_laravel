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
        $data = ['analisis' => new AnalisisCalidad(), 'modo' => 'crear'];

        if (request()->ajax()) {
            return view('calidad._form-modal', $data);
        }

        return view('calidad.form', $data);
    }

    /** Guarda un nuevo registro. */
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['is_deleted']      = 0;
        $data['usernamecalidad'] = $this->username();

        $a = AnalisisCalidad::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Análisis de calidad #{$a->id} creado correctamente."]);
        }

        return redirect()->route('operaciones.index', ['tab' => 'calidad'])
            ->with('success', "Análisis de calidad #{$a->id} creado correctamente.");
    }

    /** Formulario de edición. */
    public function edit(AnalisisCalidad $calidad)
    {
        abort_if($calidad->is_deleted, 404);
        $data = ['analisis' => $calidad, 'modo' => 'editar'];

        if (request()->ajax()) {
            return view('calidad._form-modal', $data);
        }

        return view('calidad.form', $data);
    }

    /** Actualiza un registro. */
    public function update(Request $request, AnalisisCalidad $calidad)
    {
        abort_if($calidad->is_deleted, 404);
        $data = $this->validar($request, $calidad->id);
        $calidad->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Análisis #{$calidad->id} actualizado."]);
        }

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
            'filtro'       => ['required', 'in:' . implode(',', AnalisisCalidad::FILTROS)],
            'grupocalidad' => ['required', 'in:' . implode(',', AnalisisCalidad::GRUPOS)],
            'reactor1'     => ['required', 'boolean'],
            'reactor2'     => ['required', 'boolean'],
            'reactor3'     => ['required', 'boolean'],
            'reactor4'     => ['required', 'boolean'],
            'humedad'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pi'           => ['nullable', 'numeric', 'min:0'],
            'pf'           => ['nullable', 'numeric', 'min:0'],
        ];
        foreach (array_keys(AnalisisCalidad::MEDICIONES) as $campo) {
            if ($campo === 'humedad') continue;
            $reglas[$campo] = ['nullable', 'numeric', 'min:0'];
        }

        $data = $request->validate($reglas, [
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.date'            => 'La fecha no es válida.',
            'hora.required'         => 'La hora es obligatoria.',
            'hora.date_format'      => 'La hora no tiene el formato correcto (HH:mm).',
            'turnocalidad.required' => 'El turno es obligatorio.',
            'turnocalidad.in'       => 'El turno seleccionado no es válido.',
            'filtro.required'       => 'El filtro es obligatorio.',
            'filtro.in'             => 'El filtro seleccionado no es válido.',
            'grupocalidad.required' => 'El grupo es obligatorio.',
            'grupocalidad.in'       => 'El grupo seleccionado no es válido.',
            'humedad.numeric'       => 'La humedad debe ser un número.',
            'humedad.min'           => 'La humedad no puede ser negativa.',
            'humedad.max'           => 'La humedad no puede superar 100.',
            'pi.numeric'            => 'El PI debe ser un número.',
            'pi.min'                => 'El PI no puede ser negativo.',
            'pf.numeric'            => 'El PF debe ser un número.',
            'pf.min'                => 'El PF no puede ser negativo.',
        ], [
            'turnocalidad' => 'turno',
            'grupocalidad' => 'grupo',
        ]);

        $reactores = ['reactor1', 'reactor2', 'reactor3', 'reactor4'];
        $algunoReactor = collect($reactores)->contains(fn($r) => $request->input($r) === '1');
        if (!$algunoReactor) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reactor1' => 'Debe seleccionar al menos un reactor.',
            ]);
        }

        return $data;
    }

    private function username(): string
    {
        $u = Auth::user();
        return $u->username ?: $u->name;
    }
}
