<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIdItemsAndEstadoToPiezasAusentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up(): void
    {
        Schema::table('piezas_ausentes', function (Blueprint $table) {
            // Ajusta 'after' con el nombre de la columna tras la cual quieres ubicarlas
            $table->unsignedBigInteger('id_items')->nullable()->after('id_car');
            
            // Si id_items es clave foránea a una tabla 'items', descomenta la siguiente línea:
            // $table->foreign('id_items')->references('id')->on('items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('piezas_ausentes', function (Blueprint $table) {
            // Si agregaste clave foránea arriba, descomenta esta línea primero:
            // $table->dropForeign(['id_items']);
            
            $table->dropColumn(['id_items']);
        });
    }
}
