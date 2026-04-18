<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnuladosComisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anulados_comisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoiceitem_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('item_id');
            $table->text('description')->nullable();
            $table->decimal('quantity',10,2);
            $table->decimal('unit_cost',10,2);
            $table->decimal('discount',10,2);
            $table->string('tax_method',10)->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->decimal('tax_amount',10,2)->nullable();
            $table->decimal('sub_total',10,2);
            $table->bigInteger('company_id');
            $table->bigInteger('idCar')->nullable();
            $table->bigInteger('product_id');
            $table->text('observaciones')->nullable();
            $table->string('estatus', 100);
            $table->decimal('monto_anulado',10,2);
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anulados_comisions');
    }
}
