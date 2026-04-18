<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFieldFechaUpdatedToOrdenDesarmeTable extends Migration
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
                $table->datetime('f_ingreso_puesto')->change()->nullable();
            });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orden_desarme', function (Blueprint $table) {
            //
        });
    }
}
