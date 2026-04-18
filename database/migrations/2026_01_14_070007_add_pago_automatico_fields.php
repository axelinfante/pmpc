<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoAutomaticoFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Solo agregar campos útiles a la tabla cuenta_corriente
        // Los pagos automáticos se identifican por la nota, no necesitan campos especiales
        // Pero mantenemos un campo para marcar si fue revertido para búsquedas más eficientes
        Schema::table('cuenta_corriente', function (Blueprint $table) {
            // Campo para marcar si un movimiento fue revertido (para búsquedas eficientes)
            $table->tinyInteger('fue_revertido')->default(0)->after('nota');
            
            // Referencia al movimiento de reversión (si aplica)
            $table->bigInteger('movimiento_reversion_id')->nullable()->after('fue_revertido');
            
            // Índices para mejorar performance en búsquedas
            $table->index('fue_revertido');
            $table->index('movimiento_reversion_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cuenta_corriente', function (Blueprint $table) {
            $table->dropIndex(['fue_revertido']);
            $table->dropIndex(['movimiento_reversion_id']);
            
            $table->dropColumn('fue_revertido');
            $table->dropColumn('movimiento_reversion_id');
        });
    }
}