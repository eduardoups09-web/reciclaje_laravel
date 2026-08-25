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
                    <form action="{{ route('reporte-reciclaje.exportar') }}" method="GET">
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
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-download"></i> Descargar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
