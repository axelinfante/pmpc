<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoAutomaticoFieldsToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Campo para identificar transacciones de pago automático desde cuenta corriente
            $table->tinyInteger('es_pago_automatico')->default(0)->after('note');
            
            // Campo para marcar si una transacción fue revertida
            $table->tinyInteger('transaccion_revertida')->default(0)->after('es_pago_automatico');
            
            // Campo para identificar transacciones de reversión
            $table->tinyInteger('es_reversion')->default(0)->after('transaccion_revertida');
            
            // Referencia a la transacción original que fue revertida (para reversiones)
            $table->bigInteger('transaccion_revertida_id')->nullable()->after('es_reversion');
            
            // Índices para mejorar performance en búsquedas
            $table->index('es_pago_automatico');
            $table->index('transaccion_revertida');
            $table->index('es_reversion');
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
            $table->dropIndex(['es_pago_automatico']);
            $table->dropIndex(['transaccion_revertida']);
            $table->dropIndex(['es_reversion']);
            
            $table->dropColumn('es_pago_automatico');
            $table->dropColumn('transaccion_revertida');
            $table->dropColumn('es_reversion');
            $table->dropColumn('transaccion_revertida_id');
        });
    }
}