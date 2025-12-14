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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // 'INGRESO' o 'EGRESO'
            $table->string('concepto'); // Ej: "Cobro Alquiler..."
            $table->decimal('monto', 12, 2); // Plata
            $table->dateTime('fecha');
            
            // Usuario que hizo el movimiento (opcional nullable por si el usuario se borra)
            $table->unsignedBigInteger('usuario_id')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
