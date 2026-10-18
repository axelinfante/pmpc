<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaSincronizarFactura extends Model
{
		use SoftDeletes;


		protected $fillable = [
			'token',
			'url',
			'company_id',
		];

    /**
     * Obtener la empresa asociada a esta sincronización.
     * (Asumiendo que tienes un modelo Company)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

}
