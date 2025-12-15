<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('liquidaciones', function (Blueprint $table) {
            // Los campos que agregamos antes
            $table->decimal('total_ingresos', 15, 2)->after('fecha_liquidacion')->default(0);
            $table->decimal('total_gastos', 15, 2)->after('total_ingresos')->default(0);
            $table->decimal('comision_inmobiliaria', 15, 2)->after('total_gastos')->default(0);
            $table->decimal('monto_neto', 15, 2)->after('comision_inmobiliaria')->default(0);
            
            // --- AGREGAR ESTA LÍNEA ---
            $table->text('observaciones')->nullable()->after('monto_neto'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liquidaciones', function (Blueprint $table) {
            //
        });
    }
};
