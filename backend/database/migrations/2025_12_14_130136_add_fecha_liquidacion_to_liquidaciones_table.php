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
            // Agrega la columna. Puede ser 'date' o 'dateTime'
            $table->dateTime('fecha_liquidacion')->after('periodo')->default(now());
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
