<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->string('localidad')->nullable()->after('direccion');
        });
    }

    public function down()
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('localidad');
        });
    }
};
