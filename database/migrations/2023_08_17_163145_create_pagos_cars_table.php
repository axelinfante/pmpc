<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos_cars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_car')->index();
            $table->bigInteger('id_gasto')->index();


            $table->boolean('control')->nullable();

            $table->date('fecha_limite_retiro')->nullable();
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
        Schema::dropIfExists('pagos_cars');
    }
}
