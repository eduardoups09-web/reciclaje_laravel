@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Descargar Reporte de Reciclaje</h5>
                </div>
                <div class="card-body">
                    <form id="reporteForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mes <span class="text-danger">*</span></label>
                                <select name="mes" class="form-select" required>
                                    <option value="">-- Seleccionar Mes --</option>
                                    @foreach($meses as $num => $nombre)
                                        <option value="{{ $num }}" {{ $num == (int) now()->format('m') ? 'selected' : '' }}>
                                            {{ $nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Anio <span class="text-danger">*</span></label>
                                <select name="anio" class="form-select" required>
                                    <option value="">-- Seleccionar Anio --</option>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio }}" {{ $anio == (int) now()->format('Y') ? 'selected' : '' }}>
                                            {{ $anio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-center d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success btn-lg" onclick="submitForm('{{ route('reporte-reciclaje.exportar') }}')">
                                <i class="bi bi-download"></i> Descargar Excel
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" onclick="submitForm('{{ route('reporte-reciclaje.exportar-pdf') }}')">
                                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function submitForm(url) {
        var form = document.getElementById('reporteForm');
        form.action = url;
        form.submit();
    }
</script>
@endpush
