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

    // Producción (salidas). El parámetro de modelo se llama {produccion}.
    Route::resource('produccion', ProduccionController::class)
        ->parameters(['produccion' => 'produccion'])
        ->except(['show']);

    // Calidad (analisiscalidad). El parámetro de modelo se llama {calidad}.
    Route::resource('calidad', CalidadController::class)
        ->parameters(['calidad' => 'calidad'])
        ->except(['show']);

    // Inventario / Bodega
    Route::resource('bodega', BodegaController::class)
        ->parameters(['bodega' => 'bodega'])
        ->except(['show']);

    // Saldos de inventario
    Route::resource('saldos', SaldoController::class)
        ->parameters(['saldos' => 'saldo'])
        ->except(['show']);

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
    Route::resource('movimiento-detalle', MovimientoDetalleController::class)
        ->parameters(['movimiento-detalle' => 'movimiento_detalle'])
        ->except(['show']);
});
