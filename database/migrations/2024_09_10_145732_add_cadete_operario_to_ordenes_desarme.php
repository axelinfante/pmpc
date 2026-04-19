<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCadeteOperarioToOrdenesDesarme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordenes_desarme', function (Blueprint $table) {
            //
            $table->bigInteger('idCadete_operario')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ordenes_desarme', function (Blueprint $table) {
            //
            $table->dropColumn('idCadete_operario');
        });
    }
}
