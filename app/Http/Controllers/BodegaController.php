<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BodegaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only('fecha_desde', 'fecha_hasta', 'mes', 'anio', 'peso', 'consecutivo');

        if (empty($filtros['consecutivo']) && empty($filtros['peso'])) {
            if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
                $filtros['fecha_desde'] = \Carbon\Carbon::createFromDate($filtros['anio'], $filtros['mes'], 1)->startOfMonth()->toDateString();
                $filtros['fecha_hasta'] = \Carbon\Carbon::createFromDate($filtros['anio'], $filtros['mes'], 1)->endOfMonth()->toDateString();
            } elseif (empty($filtros['mes']) && empty($filtros['anio'])) {
                $filtros['mes'] = (int) now()->format('m');
                $filtros['anio'] = (int) now()->format('Y');
                $filtros['fecha_desde'] = now()->startOfMonth()->toDateString();
                $filtros['fecha_hasta'] = now()->endOfMonth()->toDateString();
            }
        }

        $unidades = DB::table('unidad')->where('is_deleted', 0)->pluck('unidades');
        $transportistas = DB::table('transportista')->where('is_deleted', 0)->pluck('transportistas');
        $motivos = DB::table('motivo')->where('is_deleted', 0)->pluck('motivos');

        $despachosQuery = Bodega::select('fechainicio', 'despacho', 'consecutivo')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->where('is_deleted', 0);

        if (!empty($filtros['consecutivo'])) {
            $despachosQuery->where('consecutivo', $filtros['consecutivo']);
        } elseif (!empty($filtros['peso'])) {
            $despachosQuery->where('cantidad', $filtros['peso']);
        } else {
            $despachosQuery->where('fechainicio', '>=', $filtros['fecha_desde'])
                ->where('fechainicio', '<=', $filtros['fecha_hasta']);
        }

        $despachos = $despachosQuery->groupBy('fechainicio', 'despacho', 'consecutivo')
            ->orderBy('fechainicio')
            ->orderBy('despacho')
            ->get();

        $registros = Bodega::activos()
            ->filtrar($filtros)
            ->orderByDesc('fechainicio');

        if (!empty($filtros['consecutivo'])) {
            $registros->orderByDesc('consecutivo');
        }

        $registros = $registros->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('bodega.index', compact('registros', 'filtros', 'unidades', 'transportistas', 'motivos', 'despachos'));
    }

    public function consecutivo(Request $request)
    {
        $fecha    = $request->query('fecha');
        $despacho = $request->query('despacho');

        if (!$fecha || !$despacho) {
            return response()->json(['consecutivo' => 1]);
        }

        $existe = Bodega::where('fechainicio', $fecha)
            ->where('despacho', $despacho)
            ->where('is_deleted', 0)
            ->first();

        if ($existe) {
            return response()->json(['consecutivo' => $existe->consecutivo]);
        }

        $max = Bodega::where('is_deleted', 0)
            ->max('consecutivo');

        return response()->json(['consecutivo' => ($max ?? 0) + 1]);
    }

    public function pdf(Request $request)
    {
        $fecha      = $request->query('fecha');
        $despacho   = $request->query('despacho');
        $consecutivo = $request->query('consecutivo');

        $registros = Bodega::where('fechainicio', $fecha)
            ->where('despacho', $despacho)
            ->where('consecutivo', $consecutivo)
            ->where('is_deleted', 0)
            ->get();

        if ($registros->isEmpty()) {
            abort(404, 'No se encontraron registros para esta orden de despacho.');
        }

        $total = $registros->sum('cantidad');

        $pdf = Pdf::loadView('bodega.pdf', compact('registros', 'total', 'fecha', 'despacho', 'consecutivo'))
            ->setPaper('letter');

        return $pdf->stream("orden_despacho_{$consecutivo}_{$fecha}.pdf");
    }

    public function pdfFormato($id)
    {
        $zip = new \ZipArchive();
        $docxPath = 'C:/Users/BRYAN/Downloads/descarga_page-0001.docx';

        if ($zip->open($docxPath) !== true) {
            abort(500, 'No se pudo abrir el documento formato.');
        }

        $imgData = $zip->getFromName('word/media/image1.jpeg');
        $zip->close();

        if (!$imgData) {
            abort(500, 'No se encontró la imagen en el documento formato.');
        }

        $imagenBase64 = base64_encode($imgData);

        $pdf = Pdf::loadView('bodega.pdf_formato', compact('imagenBase64'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("formato_orden_despacho_{$id}.pdf");
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
            'tipobateria'         => ['required', 'string', 'max:50'],
            'contenedor'          => ['required', 'string', 'max:50'],
            'cantidad'            => ['required', 'numeric'],
            'unidad'              => ['nullable', 'string', 'max:100'],
            'consecutivo'         => ['nullable', 'integer'],
            'despacho'            => ['required', 'string', 'max:50'],
            'nombreDestinatario'  => ['nullable', 'string', 'max:100'],
            'rucDestinatario'     => ['nullable', 'string', 'max:255'],
            'nombreTransportista' => ['nullable', 'string', 'max:255'],
            'transportistaRuc'    => ['nullable', 'string', 'max:100'],
            'placaTransportista'  => ['nullable', 'string', 'max:100'],
            'observacion'         => ['nullable', 'string', 'max:100'],
            'motivo'              => ['nullable', 'string', 'max:100'],
            'partida'             => ['nullable', 'string', 'max:100'],
            'fechaemision'        => ['nullable', 'date'],
            'llegada'             => ['nullable', 'string', 'max:100'],
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
