<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeguimientoCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seguimiento_cars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idCar')->index();
            $table->boolean('motor_vendido_reservado')->nullable(); //1 si esta vendido 2 si esta reservado
            $table->date('entra_desarme')->index()->nullable();
            $table->bigInteger('idVendedorMotor')->index()->nullable();
            $table->date('traslado_notificado')->nullable();
            $table->string('traer_a')->nullable();
            $table->date('fecha_traslado')->nullable();
            $table->date('fecha_act_estado')->nullable();
            $table->string('ubicacion')->nullable();


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
        Schema::dropIfExists('seguimiento_cars');
    }
}
