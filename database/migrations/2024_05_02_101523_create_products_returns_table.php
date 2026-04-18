<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_returns', function (Blueprint $table) {
            $table->id();
            $table->date('return_date');
            $table->string('return_number');
            $table->bigInteger('invoice_id');
            $table->bigInteger('product_id');
            $table->decimal('quantity',10,2);
            $table->text('note')->nullable();
            $table->bigInteger('company_id');
            $table->string('status',10);
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
        Schema::dropIfExists('products_returns');
    }
}
