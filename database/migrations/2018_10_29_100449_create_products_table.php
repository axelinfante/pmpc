<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('item_id');
            $table->bigInteger('supplier_id')->nullable();
            $table->bigInteger('car_id')->nullable();
            $table->bigInteger('marca_modelo')->nullable();
            $table->decimal('product_cost',10,2)->nullable();
            $table->decimal('product_price',10,2)->nullable();
            $table->string('product_unit',20)->nullable();
            $table->string('tax_method',10)->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->text('description')->nullable();
            $table->text('stock')->nullable();
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
        Schema::dropIfExists('products');
    }
}
