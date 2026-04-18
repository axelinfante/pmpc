<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialStateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_state_cars', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->bigInteger('idCar')->index();
            $table->bigInteger('id_user');
            $table->bigInteger('id_current_state')->nullable();
            $table->bigInteger('id_new_state');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_state_cars');
    }
}
