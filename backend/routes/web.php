<?php

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Facades\Route;

// Ruta temporal para DISEÑAR el recibo sin generar PDF
Route::get('/ver-recibo', function () {
    // Creamos datos falsos en memoria para probar el diseño
    // (Esto simula un objeto Pago y una Cuota real)
    $pago = new Pago([
        'nro_comprobante' => 'REC-171500',
        'fecha_pago' => now(),
        'monto_pagado' => 50000,
        'forma_pago' => 'EFECTIVO',
    ]);

    // Simulamos la estructura de relaciones (esto es un truco de Laravel)
    $pago->setRelation('cuota', new Cuota([
        'periodo' => '2025-01',
        'saldo_pendiente' => 0,
    ]));

    // Simulamos datos profundos
    $inquilino = new \stdClass;
    $inquilino->nombre_completo = 'Juan Pérez';
    $propiedad = new \stdClass;
    $propiedad->direccion = 'San Martín 450';

    // Renderizamos la vista directamente
    return view('pdf.recibo', [
        'pago' => $pago,
        'cuota' => $pago->cuota,
        'inquilino' => $inquilino, // Pasamos datos extra a la vista si hace falta
        'propiedad' => $propiedad,
    ]);
});
