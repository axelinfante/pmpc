<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionRetiroCotizacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_retiro_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idInvoiceConSaldo')->nullable();
            $table->bigInteger('idClientePagar')->nullable();
            $table->bigInteger('idTransactionOld')->nullable();
            $table->bigInteger('idTransactionNew')->nullable();
            $table->bigInteger('idTransactionCC')->nullable();
            $table->decimal('monto', 10, 2)->nullable(); 
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
        Schema::dropIfExists('transaction_retiro_cotizaciones');
    }
}
