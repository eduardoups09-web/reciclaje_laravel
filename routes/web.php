<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\CalidadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\MovimientoDetalleController;
use App\Http\Controllers\MpImportController;
use App\Http\Controllers\MpNacionalController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\ReporteGerencialController;
use App\Http\Controllers\ReporteGerencialPabloController;
use App\Http\Controllers\ReporteReciclajeController;
use App\Http\Controllers\SaldoController;
use Illuminate\Support\Facades\Route;

// --- Autenticación ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- Zona privada (requiere sesión) ---
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Operaciones (módulo unificado con pestañas)
    Route::get('/operaciones', [OperacionController::class, 'index'])->name('operaciones.index');

    // Movimientos consolidados (solo lectura, agrupado por fecha+grupo+turno)
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('/movimientos/detalle', [MovimientoController::class, 'show'])->name('movimientos.show');
    Route::post('/movimientos/eliminar', [MovimientoController::class, 'destroy'])->name('movimientos.destroy');

    // Producción (salidas). El parámetro de modelo se llama {produccion}.
    Route::resource('produccion', ProduccionController::class)
        ->parameters(['produccion' => 'produccion'])
        ->except(['show']);

    // Calidad (analisiscalidad). El parámetro de modelo se llama {calidad}.
    Route::resource('calidad', CalidadController::class)
        ->parameters(['calidad' => 'calidad'])
        ->except(['show']);

    // Inventario / Bodega
    Route::get('/bodega/consecutivo', [BodegaController::class, 'consecutivo'])->name('bodega.consecutivo');
    Route::get('/bodega/pdf', [BodegaController::class, 'pdf'])->name('bodega.pdf');
    Route::get('/bodega/pdf-formato/{id}', [BodegaController::class, 'pdfFormato'])->name('bodega.pdfFormato');
    Route::resource('bodega', BodegaController::class)
        ->parameters(['bodega' => 'bodega'])
        ->except(['show']);

    // Saldos de inventario
    Route::resource('saldos', SaldoController::class)
        ->parameters(['saldos' => 'saldo'])
        ->except(['show']);
    Route::post('saldos/autollenar', [SaldoController::class, 'autollenar'])->name('saldos.autollenar');

    // MP Importación
    Route::resource('mpimport', MpImportController::class)
        ->parameters(['mpimport' => 'mpimport'])
        ->except(['show']);

    // MP Nacional
    Route::resource('mpnacional', MpNacionalController::class)
        ->parameters(['mpnacional' => 'mpnacional'])
        ->except(['show']);

    // Insumos
    Route::resource('insumos', InsumoController::class)
        ->parameters(['insumos' => 'insumo'])
        ->except(['show']);

    // Movimiento Detalle
    Route::get('movimiento-detalle/estado', [MovimientoDetalleController::class, 'obtenerEstado'])
        ->name('movimiento-detalle.obtenerEstado');
    Route::post('movimiento-detalle/estado', [MovimientoDetalleController::class, 'updateEstado'])
        ->name('movimiento-detalle.updateEstado');
    Route::resource('movimiento-detalle', MovimientoDetalleController::class)
        ->parameters(['movimiento-detalle' => 'movimiento_detalle'])
        ->except(['show']);

    // Reportes Gerenciales - Roberto
    Route::resource('reportes-gerenciales', ReporteGerencialController::class)
        ->parameters(['reportes-gerenciales' => 'reporte'])
        ->except(['show']);

    // Reportes Gerenciales - Pablo
    Route::resource('pablo', ReporteGerencialPabloController::class)
        ->parameters(['pablo' => 'reporte'])
        ->except(['show']);

    // Reporte Reciclaje (Excel)
    Route::get('/reporte-reciclaje', [ReporteReciclajeController::class, 'index'])
        ->name('reporte-reciclaje.index');
    Route::get('/reporte-reciclaje/exportar', [ReporteReciclajeController::class, 'exportar'])
        ->name('reporte-reciclaje.exportar');
});
