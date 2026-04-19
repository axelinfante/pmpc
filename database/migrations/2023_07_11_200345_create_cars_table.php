<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_asignacion')->nullable();
            $table->string('forma',50)->nullable();
            $table->bigInteger('idTramitador')->nullable()->index();
            $table->bigInteger('idAseguradora')->nullable();
            $table->string('tramitador_compania','100')->nullable();
            $table->string('siniestro',100)->nullable();
            $table->string('dominio','50')->nullable();
            $table->bigInteger('idMarca_modelo')->nullable()->index();
            $table->string('motor_nro','100')->nullable()->index();
            $table->string('tipo_baja','50')->nullable();
            $table->string('asegurado')->nullable();
            $table->string('contacto',100)->nullable();
            $table->string('lugar_retiro')->nullable();
            $table->string('localidad')->nullable();
            $table->bigInteger('provincia')->nullable();
            $table->date('fecha_entrega_asegurado_cia')->nullable();
            $table->bigInteger('entregado_a')->nullable();
            $table->string('observaciones_admin')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->boolean('coordinar_retiro')->nullable();
            $table->date('fecha_envio_doc')->nullable();
            $table->string('chasis')->nullable();
            $table->date('fecha_confirmacion_contacto')->nullable();
            $table->date('fecha_limite_retiro')->nullable();
            $table->bigInteger('idResponsable_retiro')->index()->nullable();
            $table->string('crp_nro',100)->nullable();
            $table->bigInteger('idLugar_entrega')->index()->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->bigInteger('idEstado')->index()->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->boolean('control')->nullable();
            $table->string('observacion_retiro')->nullable();


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
        Schema::dropIfExists('cars');
    }
}
