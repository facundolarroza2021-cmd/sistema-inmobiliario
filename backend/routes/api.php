<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // <--- AGREGADO (Faltaba este)
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\GastoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (No requieren token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']); // <--- ESENCIAL PARA ENTRAR

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren estar logueado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil', [AuthController::class, 'perfil']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/register', [AuthController::class, 'register']); 
        Route::delete('/propiedades/{id}', [PropiedadController::class, 'destroy']);
        Route::delete('/inquilinos/{id}', [InquilinoController::class, 'destroy']);
        Route::delete('/propietarios/{id}', [PropietarioController::class, 'destroy']); // Movido aquí por seguridad
        Route::delete('/gastos/{id}', [GastoController::class, 'destroy']); // Movido aquí por seguridad
    });

    // --- GRUPO 2: ADMINISTRATIVOS (Incluye Admin) ---
    // Pueden crear, editar y gestionar el día a día, pero NO eliminar
    Route::middleware('role:admin,administrativo')->group(function () {
        
        // Propietarios
        Route::get('/propietarios', [PropietarioController::class, 'index']);
        Route::post('/propietarios', [PropietarioController::class, 'store']);
        Route::get('/propietarios/{id}', [PropietarioController::class, 'show']);

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
    });

    // --- GRUPO 3: COBRADORES (Incluye a todos) ---
    // Solo lectura y cobro
    Route::middleware('role:admin,administrativo,cobrador')->group(function () {
        
        // Lectura general (Para buscadores y listados)
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/propiedades', [PropiedadController::class, 'index']);
        Route::get('/propiedades/{id}', [PropiedadController::class, 'show']);
        Route::get('/propiedades/{id}/gastos', [GastoController::class, 'byPropiedad']);
        
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