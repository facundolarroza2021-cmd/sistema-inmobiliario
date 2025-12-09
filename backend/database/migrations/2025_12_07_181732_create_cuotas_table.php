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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            $table->integer('numero_cuota');
            $table->string('periodo'); // "2025-01"
            $table->date('fecha_vencimiento');
            $table->decimal('monto_original', 10, 2);
            $table->decimal('saldo_pendiente', 10, 2);
            $table->string('estado')->default('PENDIENTE'); // PENDIENTE, PARCIAL, PAGADO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
