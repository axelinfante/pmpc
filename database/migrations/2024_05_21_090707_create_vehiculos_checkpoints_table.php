<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiculosCheckpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehiculos_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('checkpoint_id');
            $table->bigInteger('vehiculo_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status',20);
            $table->date('status_date')->nullable();
            $table->text('observaciones')->nullable();
            $table->bigInteger('user_id');
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
        Schema::dropIfExists('vehiculos_checkpoints');
    }
}
