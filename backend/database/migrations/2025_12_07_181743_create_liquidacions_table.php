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
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propietario_id')->constrained();
            $table->date('fecha');
            $table->string('periodo');
            $table->decimal('monto_total_cobrado', 12, 2);
            $table->decimal('comision_cobrada', 12, 2);
            $table->decimal('monto_entregado', 12, 2);
            $table->string('ruta_pdf')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacions');
    }
};
