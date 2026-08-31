@extends('layouts.app')
@section('titulo', 'Bodega')

@section('contenido')
@php use App\Models\Bodega; $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Bodega · Despachos</h3>
    <div>
        <button onclick="abrirModalBodegaCrear()" class="btn btn-success me-2">+ Nuevo</button>
        <button onclick="abrirModalTransportista()" class="btn btn-outline-success">Transportistas</button>
    </div>
</div>

{{-- Filtros --}}
<form method="get" action="{{ route('bodega.index') }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Mes</label>
            <select name="mes" class="form-select">
                <option value="">Todos</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" @selected(($filtros['mes'] ?? '') == $i)>{{ $meses[$i] }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Año</label>
            <select name="anio" class="form-select">
                <option value="">Todos</option>
                @for ($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" @selected(($filtros['anio'] ?? '') == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Peso</label>
            <input type="number" name="peso" value="{{ $filtros['peso'] ?? '' }}" class="form-control" step="0.01" placeholder="Ej: 28478">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Orden de Despacho</label>
            <input type="number" name="consecutivo" value="{{ $filtros['consecutivo'] ?? '' }}" class="form-control" placeholder="Ej: 80">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Filtrar</button>
            <a href="{{ route('bodega.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

@if ($despachos->count())
<div class="card card-body shadow-sm mb-3">
    <strong>Órdenes de Despacho:</strong>
    <div class="mt-2">
        @foreach ($despachos as $d)
            <a href="{{ route('bodega.pdf', ['fecha' => $d->fechainicio, 'despacho' => $d->despacho, 'consecutivo' => $d->consecutivo]) }}"
               target="_blank"
               class="btn btn-outline-danger btn-sm me-1 mb-1">
                Despacho {{ $d->despacho }} - Consec. {{ $d->consecutivo }} (Total: {{ $d->total_cantidad }})
            </a>
        @endforeach
    </div>
</div>
@endif

<div id="tablaRegistros">
    @include('bodega._tabla')
</div>

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
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Gestión de Transportistas --}}
<div class="modal fade" id="modalTransportista" tabindex="-1" aria-labelledby="modalTransportistaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalTransportistaLabel">Gestión de Transportistas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formTransportista" method="post" action="{{ route('transportistas.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="transportistaId">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="transportistas" id="transportistaNombre" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RUC</label>
                            <input type="text" name="ruc" id="transportistaRuc" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Placa</label>
                            <input type="text" name="placa" id="transportistaPlaca" class="form-control">
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-success" id="btnGuardarTransportista">Guardar</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reiniciarFormTransportista()">Limpiar</button>
                        <span id="transportistaModo" class="badge bg-info text-dark ms-2 d-none">Editando</span>
                    </div>
                </form>

                <hr>

                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Razón Social</th>
                                <th>RUC</th>
                                <th>Placa</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTransportistas">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
            .then(html => { modalBody.innerHTML = html; initConsecutivo(); initTransportistaAutoload(); })
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
                refrescarTabla();
            } else {
                mostrarErrores(data.errors || {});
            }
        })
        .catch(() => {
            mostrarErrores({ 'general': ['Error al guardar. Intente de nuevo.'] });
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'Guardar';
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

                const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
                input.addEventListener(eventType, function limpiarError() {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                    this.removeEventListener(eventType, limpiarError);
                }, { once: true });
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

    function refrescarTabla() {
        const cont = document.getElementById('tablaRegistros');
        if (!cont) return;

        const url = window.location.pathname + window.location.search;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.text())
        .then(html => { cont.innerHTML = html; })
        .catch(() => {});
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

        const autoLlenados = {
            'nombreDestinatario': 'FRABRICA DE BATERIA FABRIBAT CIA.LTDA',
            'rucDestinatario': '1791398262001',
            'partida': 'Km 291/2 vida Daule Petrillo',
            'llegada': 'PANAMERICANA NORTE 71/2'
        };

        Object.entries(autoLlenados).forEach(([name, valor]) => {
            const input = form.querySelector(`[name="${name}"]`);
            if (input) {
                input.addEventListener('focus', () => {
                    if (!input.value) input.value = valor;
                });
            }
        });
    }

    function initTransportistaAutoload() {
        const select = document.getElementById('nombreTransportista');
        if (!select) return;
        select.addEventListener('change', function() {
            const nombre = this.value;
            if (!nombre) return;
            fetch(`/transportistas/obtener?nombre=${encodeURIComponent(nombre)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const ruc = document.querySelector('[name="transportistaRuc"]');
                const placa = document.querySelector('[name="placaTransportista"]');
                if (ruc) ruc.value = data.ruc || '';
                if (placa) placa.value = data.placa || '';
            });
        });
    }

    let modoEdicionTransportista = false;

    function cargarTransportistas() {
        fetch('/transportistas/listar', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('tablaTransportistas');
            tbody.innerHTML = '';

            if (!data.success || !data.items.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin transportistas registrados.</td></tr>';
                return;
            }

            data.items.forEach(t => {
                const tr = document.createElement('tr');

                const td1 = document.createElement('td');
                td1.textContent = t.transportistas;
                tr.appendChild(td1);

                const td2 = document.createElement('td');
                td2.textContent = t.ruc || '';
                tr.appendChild(td2);

                const td3 = document.createElement('td');
                td3.textContent = t.placa || '';
                tr.appendChild(td3);

                const td4 = document.createElement('td');
                td4.className = 'text-end text-nowrap';

                const btnEdit = document.createElement('button');
                btnEdit.type = 'button';
                btnEdit.className = 'btn btn-sm btn-outline-primary';
                btnEdit.textContent = 'Editar';
                btnEdit.addEventListener('click', () => editarTransportista(t.id, t.transportistas, t.ruc || '', t.placa || ''));

                const btnDel = document.createElement('button');
                btnDel.type = 'button';
                btnDel.className = 'btn btn-sm btn-outline-danger';
                btnDel.textContent = 'Eliminar';
                btnDel.addEventListener('click', () => eliminarTransportista(t.id));

                td4.appendChild(btnEdit);
                td4.appendChild(document.createTextNode(' '));
                td4.appendChild(btnDel);
                tr.appendChild(td4);

                tbody.appendChild(tr);
            });
        })
        .catch(() => {
            document.getElementById('tablaTransportistas').innerHTML =
                '<tr><td colspan="4" class="text-center text-danger py-3">Error al cargar.</td></tr>';
        });
    }

    function refrescarSelectsTransportista() {
        fetch('/transportistas/listar', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            document.querySelectorAll('#nombreTransportista').forEach(sel => {
                const actual = sel.value;
                sel.innerHTML = '';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = 'Seleccionar…';
                sel.appendChild(opt0);
                data.items.forEach(t => {
                    const o = document.createElement('option');
                    o.value = t.transportistas;
                    o.textContent = t.transportistas;
                    sel.appendChild(o);
                });
                sel.value = actual;
            });
        })
        .catch(() => {});
    }

    function reiniciarFormTransportista() {
        modoEdicionTransportista = false;
        const form = document.getElementById('formTransportista');
        form.reset();
        document.getElementById('transportistaId').value = '';
        document.getElementById('transportistaModo').classList.add('d-none');
        document.getElementById('btnGuardarTransportista').textContent = 'Guardar';
    }

    function tokenCsrfActual() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('#formTransportista input[name="_token"]')?.value
            || '';
    }

    function refrescarTokenCsrf() {
        return fetch('/csrf-token', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            const t = data.token;
            const input = document.querySelector('#formTransportista input[name="_token"]');
            if (input) input.value = t;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', t);
            return t;
        })
        .catch(() => tokenCsrfActual());
    }

    async function enviarTransportista(url, method, formData) {
        // Laravel/HTTP: PHP solo llena $_POST en POST, así que para PUT/DELETE
        // usamos spoofing de método (POST + _method) para que el body llegue.
        formData.append('_method', method);
        const token = await refrescarTokenCsrf();
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        };
        let res = await fetch(url, { method: 'POST', body: formData, headers, credentials: 'same-origin' });
        if (res.status === 419) {
            const token2 = await refrescarTokenCsrf();
            headers['X-CSRF-TOKEN'] = token2;
            res = await fetch(url, { method: 'POST', body: formData, headers, credentials: 'same-origin' });
        }
        return res;
    }

    window.editarTransportista = function(id, nombre, ruc, placa) {
        modoEdicionTransportista = true;
        document.getElementById('transportistaId').value = id;
        document.getElementById('transportistaNombre').value = nombre;
        document.getElementById('transportistaRuc').value = ruc;
        document.getElementById('transportistaPlaca').value = placa;
        document.getElementById('transportistaModo').classList.remove('d-none');
        document.getElementById('btnGuardarTransportista').textContent = 'Actualizar';
        document.getElementById('transportistaNombre').focus();
    };

    window.eliminarTransportista = async function(id) {
        if (!confirm('¿Eliminar este transportista?')) return;
        const token = await refrescarTokenCsrf();
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        };
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        let res = await fetch(`/transportistas/${id}`, { method: 'POST', body: fd, headers, credentials: 'same-origin' });
        if (res.status === 419) {
            headers['X-CSRF-TOKEN'] = await refrescarTokenCsrf();
            res = await fetch(`/transportistas/${id}`, { method: 'POST', body: fd, headers, credentials: 'same-origin' });
        }
        res.json().then(data => {
            if (data.success) {
                cargarTransportistas();
                refrescarSelectsTransportista();
            } else {
                alert('No se pudo eliminar.');
            }
        }).catch(() => alert('Error al eliminar.'));
    };

    window.abrirModalTransportista = async function() {
        await refrescarTokenCsrf();
        reiniciarFormTransportista();
        cargarTransportistas();
        const modal = new bootstrap.Modal(document.getElementById('modalTransportista'));
        modal.show();
    };

    (function initFormTransportista() {
        const formTrans = document.getElementById('formTransportista');
        if (!formTrans) return;
        const btn = document.getElementById('btnGuardarTransportista');

        formTrans.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('transportistaId').value;
            const url = (modoEdicionTransportista && id) ? `/transportistas/${id}` : formTrans.action;
            const method = (modoEdicionTransportista && id) ? 'PUT' : 'POST';

            const formData = new FormData(formTrans);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

            enviarTransportista(url, method, formData)
            .then(async res => res.json())
            .then(data => {
                if (data.success) {
                    reiniciarFormTransportista();
                    cargarTransportistas();
                    refrescarSelectsTransportista();
                } else {
                    alert(data.message || 'No se pudo guardar el transportista.');
                }
            })
            .catch(() => alert('Error al guardar el transportista.'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = modoEdicionTransportista ? 'Actualizar' : 'Guardar';
            });
        });
    })();

})();
</script>
@endpush
