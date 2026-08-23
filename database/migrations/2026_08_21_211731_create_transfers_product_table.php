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
        Schema::create('transfers_products', function (Blueprint $table) {
            $table->id();
			$table->foreignId('transfers_id')->constrained()->onDelete('cascade');
			$table->foreignId('product_id')->constrained()->onDelete('cascade');
			$table->integer('cantidad');
			   // true = recibido, false = faltante o rechazado, null = pendiente de verificar
			$table->boolean('recibido')->nullable()->default(null); 
			$table->timestamp('fecha_recibido')->nullable(); 
            $table->timestamps();
			
			$table->index('product_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers_products');
    }
};
