<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\CalidadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovimientoController;
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
});
