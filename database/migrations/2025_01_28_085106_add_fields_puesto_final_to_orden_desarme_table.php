<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsPuestoFinalToOrdenDesarmeTable extends Migration
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
            $table->string('puesto_final',10)->nullable();
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
			$table->dropColumn('puesto_final');
        });
    }
}
