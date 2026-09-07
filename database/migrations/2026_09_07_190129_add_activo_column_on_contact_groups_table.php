<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivoColumnOnContactgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_groups', function (Blueprint $table) {
            //
	        $table->enum('activo', ['Si', 'No'])->default('Si')->before('created_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_groups', function (Blueprint $table) {
            //
            $table->dropColumn('activo');

        });
    }
}
