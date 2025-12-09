<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garantes', function (Blueprint $table) {
            $table->id();
            
            // Relación con el Contrato (Si borras contrato, chau garantes)
            $table->foreignId('contrato_id')->constrained('contratos')->onDelete('cascade');
            
            $table->string('nombre_completo');
            $table->string('dni');
            $table->string('telefono')->nullable();
            
            // Ejemplo: 'PROPIETARIA', 'RECIBO', 'CAUCION'
            $table->string('tipo_garantia')->default('RECIBO'); 
            
            // Ejemplo: "Calle Falsa 123" o "Empresa Coca Cola"
            $table->text('detalle_garantia')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantes');
    }
};
