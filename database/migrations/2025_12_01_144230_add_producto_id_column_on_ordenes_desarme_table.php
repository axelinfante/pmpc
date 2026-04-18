<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductoidColumnOnOrdenesDesarmeTable extends Migration
	{ 

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ordenes_desarme', function (Blueprint $table) {
            //
             $table->bigInteger('product_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ordenes_desarme', function (Blueprint $table) {
            //
			$table->dropColumn('product_id');
        });
    }
}
