<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            $table->foreignId('inquilino_id')->nullable()->constrained()->onDelete('set null'); // Puede reportarlo el dueño
            $table->string('titulo'); // Ej: "Humedad en techo baño"
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['BAJA', 'MEDIA', 'ALTA', 'URGENTE'])->default('MEDIA');
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'RESUELTO', 'CANCELADO'])->default('PENDIENTE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
