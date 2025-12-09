<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\GastoController;

Route::get('/propietarios', [PropietarioController::class, 'index']);
Route::post('/propietarios', [PropietarioController::class, 'store']);

Route::get('/inquilinos', [InquilinoController::class, 'index']);
Route::post('/inquilinos', [InquilinoController::class, 'store']);
Route::put('/inquilinos/{id}', [InquilinoController::class, 'update']);
Route::delete('/inquilinos/{id}', [InquilinoController::class, 'destroy']);

Route::get('/propiedades', [PropiedadController::class, 'index']);
Route::post('/propiedades', [PropiedadController::class, 'store']);

Route::post('/contratos', [ContratoController::class, 'store']); 

Route::post('/pagos', [PagoController::class, 'store']); 
Route::post('/pagos/multiple', [PagoController::class, 'storeMultiple']);

Route::get('/cuotas', [CuotaController::class, 'index']);

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::post('/liquidaciones', [LiquidacionController::class, 'store']);
Route::get('/liquidaciones', [LiquidacionController::class, 'index']);

Route::get('/contratos', [ContratoController::class, 'index']);
Route::patch('/contratos/{id}/finalizar', [ContratoController::class, 'finalizar']);

Route::get('/propietarios/{id}', [PropietarioController::class, 'show']);
Route::put('/propietarios/{id}', [PropietarioController::class, 'update']);
Route::delete('/propietarios/{id}', [PropietarioController::class, 'destroy']);

Route::get('/propiedades/{id}', [PropiedadController::class, 'show']);
Route::put('/propiedades/{id}', [PropiedadController::class, 'update']);
Route::delete('/propiedades/{id}', [PropiedadController::class, 'destroy']);

Route::post('/propiedades/{id}/fotos', [PropiedadController::class, 'uploadFoto']);

Route::get('/propiedades/{id}/gastos', [GastoController::class, 'byPropiedad']);
Route::post('/gastos', [GastoController::class, 'store']);
Route::delete('/gastos/{id}', [GastoController::class, 'destroy']);