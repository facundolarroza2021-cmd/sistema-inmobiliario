<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Creamos la columna 'role' con los 3 perfiles que definimos
            $table->enum('role', ['admin', 'administrativo', 'cobrador'])
                ->default('administrativo') // Por defecto, nadie es admin
                ->after('email');

            $table->boolean('activo')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'activo']);
        });
    }
};
