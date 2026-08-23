<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
			$table->date('fecha_traslado');
			$table->string('reference');
			$table->text('detalles')->nullable();
			$table->string('status', 20)->default('pendiente');       // pendiente, en transito, entregado
		    $table->timestamp('fecha_recibido')->nullable(); 
			$table->unsignedBigInteger('user_id');
			$table->foreignId('almacen_origen_id')->constrained('lugar_entregas')->onDelete('cascade');
			$table->foreignId('almacen_destino_id')->constrained('lugar_entregas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
	
	

	
	
};
