<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
			$table->bigInteger('tipo_comprobante_id')->nullable();
            $table->string('razon_social', 100)->nullable();
            $table->string('banco', 100)->nullable();
            $table->string('cheque_nro',100)->nullable();
            $table->string('cheque_vencimiento',100)->nullable();
            $table->string('cheque_entregado_a',100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
			$table->dropColumn('tipo_comprobante_id');
            $table->dropColumn('razon_social');
            $table->dropColumn('banco');
            $table->dropColumn('cheque_nro');
            $table->dropColumn('cheque_vencimiento');
            $table->dropColumn('cheque_entregado_a');
        });
    }
}
