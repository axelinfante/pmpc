<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFechasToQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            //
            $table->date('fecha_entrega')->nullable();
            $table->boolean('retiro')->nullable(); //envio o retiro
            $table->string('entregado_a')->nullable();
            $table->bigInteger('entregado_por')->nullable()->index();//user
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            //

            $table->dropColumn('fecha_entrega');
            $table->dropColumn('retiro');
            $table->dropColumn('entregado_a');
            $table->dropColumn('entregado_por');
        });
    }
}
