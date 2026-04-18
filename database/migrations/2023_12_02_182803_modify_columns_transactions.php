<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
            $table->bigInteger('account_id')->nullable()->change();
            $table->bigInteger('chart_id')->nullable()->change();
            $table->decimal('amount',10,2)->nullable()->change();
            $table->bigInteger('payment_method_id')->nullable()->change();

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
            $table->bigInteger('account_id')->change();
            $table->bigInteger('chart_id')->change();
            $table->decimal('amount',10,2)->change();
            $table->bigInteger('payment_method_id')->change();

        });
    }
}
