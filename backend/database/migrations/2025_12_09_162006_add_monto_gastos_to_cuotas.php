<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            // Agregamos columna para acumular gastos (inicia en 0)
            $table->decimal('monto_gastos', 10, 2)->default(0)->after('monto_original');
        });
    }
    
    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropColumn('monto_gastos');
        });
    }
};
