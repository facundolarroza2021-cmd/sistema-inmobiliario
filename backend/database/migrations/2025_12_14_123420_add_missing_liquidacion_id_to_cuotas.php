<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('cuotas', function (Blueprint $table) {
        if (!Schema::hasColumn('cuotas', 'liquidacion_id')) {
            $table->foreignId('liquidacion_id')->nullable()->after('estado')->constrained('liquidaciones')->nullOnDelete();
        }
    });
}

    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            if (Schema::hasColumn('cuotas', 'liquidacion_id')) {
                $table->dropForeign(['liquidacion_id']);
                $table->dropColumn('liquidacion_id');
            }
        });
    }
};
