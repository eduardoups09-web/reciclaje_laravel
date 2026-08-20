@extends('layouts.app')
@section('titulo', 'Bodega')

@section('contenido')
@php use App\Models\Bodega; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-truck text-success"></i> Bodega · Despachos</h3>
    <button onclick="abrirModalBodegaCrear()" class="btn btn-success"><i class="bi bi-plus-lg"></i> Nuevo</button>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('bodega.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha Desde</label>
            <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <a href="{{ route('bodega.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

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
                    <td class="small">{{ $r->transportistaRuc }}</td>
                    <td class="small">{{ $r->placaTransportista }}</td>
                    <td class="small">{{ $r->observacion }}</td>
                    <td class="small">{{ $r->motivo }}</td>
                    <td class="small">{{ $r->partida }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('bodega.edit', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="{{ route('bodega.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

<div class="mt-3">{{ $registros->links() }}</div>

{{-- Modal Nuevo/Editar Movimiento Bodega --}}
<div class="modal fade" id="modalBodega" tabindex="-1" aria-labelledby="modalBodegaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalBodegaLabel">Nuevo movimiento de bodega</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalBodegaBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarBodega">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const modal = new bootstrap.Modal(document.getElementById('modalBodega'));
    const modalBody = document.getElementById('modalBodegaBody');
    const modalTitle = document.getElementById('modalBodegaLabel');
    const btnGuardar = document.getElementById('btnGuardarBodega');

    window.abrirModalBodegaCrear = function() {
        modalTitle.textContent = 'Nuevo movimiento de bodega';
        btnGuardar.onclick = guardarCrear;
        cargarForm('{{ route("bodega.create") }}');
    };

    window.abrirModalBodegaEditar = function(id) {
        modalTitle.textContent = 'Editar movimiento #' + id;
        btnGuardar.onclick = () => guardarEditar(id);
        cargarForm('/bodega/' + id + '/edit');
    };

    function cargarForm(url) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => { modalBody.innerHTML = html; initConsecutivo(); })
            .catch(() => { modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario.</div>'; });
    }

    function guardarCrear() {
        enviarForm('{{ route("bodega.store") }}', 'POST');
    }

    function guardarEditar(id) {
        const form = document.getElementById('formBodega');
        const method = form.querySelector('input[name="_method"]');
        enviarForm('/bodega/' + id, method ? method.value : 'PUT');
    }

    function enviarForm(url, method) {
        const form = document.getElementById('formBodega');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        formData.set('_token', csrfToken);
        formData.set('_method', method);

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                mostrarExito(data.message);
            } else {
                mostrarErrores(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErrores({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
    }

    function mostrarErrores(errors) {
        modalBody.querySelectorAll('.text-danger').forEach(el => el.remove());
        modalBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        for (const [campo, msgs] of Object.entries(errors)) {
            const input = modalBody.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = msgs[0];
                input.parentElement.appendChild(div);
            }
        }
    }

    function mostrarExito(mensaje) {
        modalBody.querySelectorAll('.alert-success').forEach(el => el.remove());
        const div = document.createElement('div');
        div.className = 'alert alert-success alert-dismissible fade show mt-2';
        div.innerHTML = mensaje + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        modalBody.prepend(div);
    }
    function initConsecutivo() {
        const form = document.getElementById('formBodega');
        if (!form) return;

        const fechaInput = form.querySelector('[name="fechainicio"]');
        const despachoSelect = form.querySelector('[name="despacho"]');

        let hiddenInput = form.querySelector('input[name="consecutivo"][type="hidden"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'consecutivo';
            form.appendChild(hiddenInput);
        }

        function obtenerConsecutivo() {
            const fecha = fechaInput.value;
            const despacho = despachoSelect.value;
            if (!fecha || !despacho) return;

            fetch(`/bodega/consecutivo?fecha=${fecha}&despacho=${despacho}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                hiddenInput.value = data.consecutivo;
            });
        }

        fechaInput.addEventListener('change', obtenerConsecutivo);
        despachoSelect.addEventListener('change', obtenerConsecutivo);

        const contenedorInput = form.querySelector('[name="contenedor"]');
        if (contenedorInput) {
            contenedorInput.addEventListener('focus', () => {
                if (!contenedorInput.value) {
                    contenedorInput.value = 'LOCAL';
                }
            });
        }
    }

})();
</script>
@endpush
