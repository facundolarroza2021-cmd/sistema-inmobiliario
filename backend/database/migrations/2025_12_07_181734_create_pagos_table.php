<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuota_id')->constrained();
            $table->date('fecha_pago');
            $table->decimal('monto_pagado', 12, 2);
            $table->string('forma_pago');
            $table->string('nro_comprobante')->nullable();
            $table->string('ruta_pdf')->nullable(); // Para el archivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
