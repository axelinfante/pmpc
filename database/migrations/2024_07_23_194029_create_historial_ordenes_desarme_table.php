<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialOrdenesDesarmeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_ordenes_desarmes', function (Blueprint $table) {
            $table->id();
            $table->text('informe');
            $table->bigInteger('id_user')->index();

            $table->bigInteger('id_orden_desarme')->nullable()->index();
            $table->string('pedido_pasado')->nullable();
            $table->string('prioridad')->nullable();
            $table->string('interno')->nullable();
            $table->bigInteger('id_cotizacion')->nullable()->index();
            $table->bigInteger('id_venta')->nullable()->index();
            $table->date('fecha_venta')->nullable();
            $table->string('lugar_venta')->nullable();//idDeposito
            $table->bigInteger('marca_modelo')->nullable();
            $table->bigInteger('pieza')->index();//producto
            $table->string('detalle_pieza')->nullable();
            $table->string('detalle_anulado')->nullable();
            $table->bigInteger('ubicacion')->nullable();
            $table->string('estado')->nullable();
            $table->string('autorizo')->nullable();
            $table->date('fecha_estimada_pieza_disponible')->nullable();
            $table->string('existe')->nullable();
            $table->string('falta')->nullable();
            $table->string('informo_ausencia')->nullable()->index();
            $table->string('obs_desarme_busqueda')->nullable();
            $table->date('fecha_desarmado_anulado')->nullable();
            $table->date('cargando_camioneta')->nullable();
            $table->string('entregado')->nullable();
            $table->date('fecha_embalado')->nullable();
            $table->date('fecha_avisado_vendedor')->nullable();
            $table->bigInteger('idCar')->nullable()->index();
            $table->bigInteger('idGerente')->nullable();
            $table->boolean('procesar')->nullable();


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
        Schema::dropIfExists('historial_ordenes_desarme');
    }
}
