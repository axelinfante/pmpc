<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordenes_despacho', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceitem_id');
            $table->unsignedBigInteger('invoice_id');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->bigInteger('company_id');
            $table->string('estatus', 100);
            $table->datetime('f_otro_deposito')->nullable();
            $table->datetime('f_deposito')->nullable();
            $table->datetime('f_embalado')->nullable();
            $table->string('lugar_embalado', 100);
            $table->datetime('f_entrega')->nullable();
            $table->string('forma_entrega', 100);
            $table->string('despachado_por', 100);
            $table->text('observaciones')->nullable();
            $table->string('foto_guia', 100);
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
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
        Schema::dropIfExists('dispatch_orders');
    }
}
