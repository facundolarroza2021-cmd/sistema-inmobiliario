<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('inquilinos', function (Blueprint $table) {
        $table->string('garante_nombre')->nullable()->after('telefono');
        $table->string('garante_dni')->nullable()->after('garante_nombre');
    });
}

public function down(): void
{
    Schema::table('inquilinos', function (Blueprint $table) {
        $table->dropColumn(['garante_nombre', 'garante_dni']);
    });
}
};
