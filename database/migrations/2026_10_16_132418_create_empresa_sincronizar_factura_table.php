<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaSincronizarFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_sincronizar_facturas', function (Blueprint $table) {
			  $table->id();
			  $table->string('token');
			  $table->string('url');
			  $table->bigInteger('company_id');
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
        Schema::dropIfExists('empresa_sincronizar_facturas');
    }
}
