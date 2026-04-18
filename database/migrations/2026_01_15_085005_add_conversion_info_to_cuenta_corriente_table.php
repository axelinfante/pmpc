<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionInfoToCuentaCorrienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuenta_corriente', function (Blueprint $table) {
            // Campos para información de conversión
            $table->decimal('monto_original', 15, 2)->nullable()->after('tasa_cambio');
            $table->string('moneda_original', 3)->nullable()->after('monto_original');
            $table->decimal('monto_convertido', 15, 2)->nullable()->after('moneda_original');
            $table->string('moneda_convertida', 3)->nullable()->after('monto_convertido');
            $table->decimal('tasa_aplicada', 15, 4)->nullable()->after('moneda_convertida');
            $table->boolean('tiene_conversion')->default(false)->after('tasa_aplicada');
            $table->text('detalle_conversion')->nullable()->after('tiene_conversion');
            $table->decimal('monto_aplicado', 15, 2)->nullable()->after('detalle_conversion');
            $table->string('moneda_aplicada', 3)->nullable()->after('monto_aplicado');
            $table->decimal('sobrante', 15, 2)->nullable()->after('moneda_aplicada');
            $table->string('moneda_sobrante', 3)->nullable()->after('sobrante');
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
            $table->dropColumn([
                'monto_original',
                'moneda_original',
                'monto_convertido',
                'moneda_convertida',
                'tasa_aplicada',
                'tiene_conversion',
                'detalle_conversion',
                'monto_aplicado',
                'moneda_aplicada',
                'sobrante',
                'moneda_sobrante'
            ]);
        });
    }
}