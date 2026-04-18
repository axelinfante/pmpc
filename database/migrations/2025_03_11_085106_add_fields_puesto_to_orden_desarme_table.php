<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsPuestoToOrdenDesarmeTable extends Migration
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
            $table->string('puesto',10)->nullable();
            $table->date('f_ingreso_puesto')->nullable();
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
        });
    }
}
