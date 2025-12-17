<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\IndexacionController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (No requieren token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren estar logueado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/perfil', [AuthController::class, 'perfil']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // --- GRUPO 2: ADMINISTRATIVOS (Incluye Admin) ---
    Route::middleware('role:admin,administrativo')->group(function () {


        // En routes/api.php
        Route::get('/liquidaciones/previsualizar/{propietarioId}', [LiquidacionController::class, 'previsualizar']);
        // Propietarios
        Route::get('/propietarios', [PropietarioController::class, 'index']);
        Route::post('/propietarios', [PropietarioController::class, 'store']);
        Route::get('/propietarios/{id}', [PropietarioController::class, 'show']);
        Route::put('/propietarios/{id}', [PropietarioController::class, 'update']);
        Route::delete('/propietarios/{id}', [PropietarioController::class, 'destroy']);

        // Propiedades (Edición)
        Route::post('/propiedades', [PropiedadController::class, 'store']);
        Route::put('/propiedades/{id}', [PropiedadController::class, 'update']);
        Route::post('/propiedades/{id}/fotos', [PropiedadController::class, 'uploadFoto']);

        // Inquilinos y Contratos (Gestión)
        Route::post('/inquilinos', [InquilinoController::class, 'store']);
        Route::put('/inquilinos/{id}', [InquilinoController::class, 'update']);
        Route::post('/contratos', [ContratoController::class, 'store']);
        Route::patch('/contratos/{id}/finalizar', [ContratoController::class, 'finalizar']);

        // Finanzas (Crear gastos y liquidaciones)
        Route::post('/liquidaciones', [LiquidacionController::class, 'store']);
        Route::post('/gastos', [GastoController::class, 'store']);

        Route::get('/caja', [CajaController::class, 'index']);
        Route::post('/caja', [CajaController::class, 'store']);
        Route::get('/caja/balance', [CajaController::class, 'balance']);

        Route::get('/tickets', [TicketController::class, 'index']);
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{id}', [TicketController::class, 'update']);

        Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
        Route::delete('/propiedades/{id}', [PropiedadController::class, 'destroy']);
        Route::delete('/inquilinos/{id}', [InquilinoController::class, 'destroy']);
        Route::delete('/gastos/{id}', [GastoController::class, 'destroy']);
        Route::delete('/caja/{id}', [CajaController::class, 'destroy']);

        Route::prefix('indexacion')->group(function () {
            // Endpoint para previsualizar los contratos afectados antes de aplicar el ajuste
            Route::post('previsualizar', [IndexacionController::class, 'previsualizar']);
            
            // Endpoint para aplicar el ajuste masivo a los contratos seleccionados
            Route::post('aplicar', [IndexacionController::class, 'aplicar']);
        });
    });

    //  COBRADORES ---
    Route::middleware('role:admin,administrativo,cobrador')->group(function () {

        // Lectura general (Para buscadores y listados)
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/propiedades', [PropiedadController::class, 'index']);
        Route::get('/propiedades/{id}', [PropiedadController::class, 'show']);
        Route::get('/propiedades/{id}/gastos', [GastoController::class, 'byPropiedad']);

        Route::get('/cuotas/deudas', [CuotaController::class, 'getDeudasPendientes']);

        Route::get('/inquilinos', [InquilinoController::class, 'index']);
        Route::get('/contratos', [ContratoController::class, 'index']);
        Route::get('/cuotas', [CuotaController::class, 'index']);
        Route::get('/liquidaciones', [LiquidacionController::class, 'index']);

        // Cobros (Función principal del cobrador)
        Route::post('/pagos', [PagoController::class, 'store']);
        Route::post('/pagos/multiple', [PagoController::class, 'storeMultiple']);
        Route::get('/recibos/{id}', [PagoController::class, 'generarPdf']);
    });

});
