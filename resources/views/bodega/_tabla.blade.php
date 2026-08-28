<div class="text-muted small mb-2">{{ number_format($registros->total()) }} registro(s) encontrados.</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha Inicio</th><th>Fecha Entrega</th><th>Despacho</th><th>Tipo</th><th>Contenedor</th>
                    <th class="text-end">Cantidad</th><th>Unidad</th>
                    <th>Consec.</th><th>Destinatario</th><th>RUC Dest.</th><th>Llegada</th>
                    <th>Transportista</th><th>RUC Trans.</th><th>Placa</th>
                    <th>Observación</th><th>Motivo</th><th>Partida</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($registros as $r)
                <tr>
                    <td>{{ $r->fechainicio }}</td>
                    <td>{{ $r->fechaemision }}</td>
                    <td>{{ $r->despacho }}</td>
                    <td class="small">{{ $r->tipobateria }}</td>
                    <td>{{ $r->contenedor }}</td>
                    <td class="text-end">{{ $r->cantidad }}</td>
                    <td>{{ $r->unidad }}</td>
                    <td>{{ $r->consecutivo }}</td>
                    <td class="small">{{ $r->nombreDestinatario }}</td>
                    <td class="small">{{ $r->rucDestinatario }}</td>
                    <td>{{ $r->llegada }}</td>
                    <td class="small text-muted">{{ $r->nombreTransportista }}</td>
                    <td class="small">{{ $r->rucTransportista }}</td>
                    <td class="small">{{ $r->placaTransportista }}</td>
                    <td class="small">{{ $r->observacion }}</td>
                    <td class="small">{{ $r->motivo }}</td>
                    <td class="small">{{ $r->partida }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('bodega.edit', $r) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form method="post" action="{{ route('bodega.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="18" class="text-center text-muted py-4">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $registros->links('pagination::bootstrap-5') }}</div>
