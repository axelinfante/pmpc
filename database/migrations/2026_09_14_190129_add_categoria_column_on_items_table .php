<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoriaColumnOnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            //
			$table->string('categoria', 80)->default('');
			$table->string('type', 50)->default('');
			$table->text('combo_producto')->nullable();
	        $table->enum('con_oblea', ['Si', 'No'])->default('No');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            //
			$table->dropColumn('categoria');
			$table->dropColumn('type');
			$table->dropColumn('combo_producto');
            $table->dropColumn('con_oblea');
        });
    }
}
