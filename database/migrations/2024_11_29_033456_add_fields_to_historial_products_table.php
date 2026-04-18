<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToHistorialProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historial_products', function (Blueprint $table) {
            //

            $table->bigInteger('marca_modelo')->nullable();
            $table->text('description')->nullable();
            $table->string('estado')->nullable();
            $table->string('nro_motor')->nullable();
            $table->string('nro_oblea')->nullable();
            $table->bigInteger('idDeposito')->nullable();
            $table->string('ubicacion')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historial_products', function (Blueprint $table) {
            //
        });
    }
}
