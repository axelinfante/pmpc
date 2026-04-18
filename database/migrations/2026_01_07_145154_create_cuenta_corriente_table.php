<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentaCorrienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuenta_corriente', function (Blueprint $table) {
            $table->id();
            
            // Relación con el cliente/proveedor (ajusta el nombre de la tabla si es necesario)
            $table->unsignedBigInteger('payer_payee_id')->index();
            
            // Polimorfismo para vincular con 'invoices' o 'transactions'
            $table->string('comprobable_type');
            $table->unsignedBigInteger('comprobable_id');
            $table->index(['comprobable_type', 'comprobable_id']);

            // --- Bloque de Pesos ---
            $table->decimal('debe_peso', 15, 2)->default(0);
            $table->decimal('haber_peso', 15, 2)->default(0);
            $table->decimal('saldo_peso', 15, 2)->default(0);

            // --- Bloque de Dólares ---
            $table->decimal('debe_usd', 15, 2)->default(0);
            $table->decimal('haber_usd', 15, 2)->default(0);
            $table->decimal('saldo_usd', 15, 2)->default(0);

            // --- Datos de Referencia ---
            $table->decimal('tasa_cambio', 15, 2)->default(1);
            $table->text('nota')->nullable();
            
            // Auditoría básica
            $table->timestamps();

            // Índice compuesto para velocidad en reportes de estado de cuenta
            $table->index(['payer_payee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cuenta_corrientes');
    }
}
