<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosSincronizarFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datos_sincronizar_facturas', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('invoice_id');
			$table->json('json_pmpc')->nullable();
			$table->json('json_afip')->nullable();
			$table->string('factura_fiscal', 100)->nullable();
			$table->enum('estado', ['pendiente', 'procesando', 'completado', 'error'])->default('pendiente');
			$table->text('mensaje_error')->nullable();
            $table->timestamps();
			$table->softDeletes(); 
        });
    }
	

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('datos_sincronizar_facturas');
    }
}
