<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatosEnvToContactoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
			$table->string('nombre_env',60);
			$table->string('apellidos_env',60)->nullable();
			$table->string('dni_env',60);
			$table->string('calle_env',100);
			$table->string('numero_env',30)->nullable();
			$table->string('piso_env',30)->nullable();
			$table->string('depto_env',30)->nullable();
			$table->string('cp_env',30)->nullable();
			$table->string('localidad_env',30)->nullable();
			$table->string('pcia_env',30)->nullable();
			$table->string('tel_env',30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('nombre_env');
			$table->dropColumn('apellidos_env');
			$table->dropColumn('dni_env');
			$table->dropColumn('calle_env');
			$table->dropColumn('numero_env');
			$table->dropColumn('piso_env');
			$table->dropColumn('depto_env');
			$table->dropColumn('cp_env');
			$table->dropColumn('localidad_env');
			$table->dropColumn('pcia_env');
			$table->dropColumn('tel_env');
        });
    }
}
