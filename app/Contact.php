<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';

    protected $guarded = [];

	public function group()
    {
        return $this->hasOne('App\ContactGroup','id','group_id')->withDefault();
    }

	public function user()
    {
        return $this->belongsTo('App\User')->withDefault();
    }

    public function company()
    {
        return $this->belongsTo('App\Company','company_id')->withDefault();
    }

    public function projects()
    {
        return $this->hasMany('App\Project','client_id');
    }

    public function cuentasCorrientes()
    {
        return $this->hasMany('App\CuentaCorriente','payer_payee_id');
    }
	
	
	/**
     * Filtra los clientes de la compañía actual más los que tienen cotizaciones en ella.
     */
    public function scopeParaVentas(Builder $query, $companyId): Builder
{
    $companyIds = Arr::wrap($companyId);

    return $query->where(function ($q) use ($companyIds) {
        $q->whereIn('contacts.company_id', $companyIds)
          ->orWhereHas('invoices', function ($subQuery) use ($companyIds) {
              $subQuery->whereIn('invoices.company_id', $companyIds);
          });
    })
    ->groupBy('contacts.id');

     /*return $query->distinct()->where(function ($q) use ($companyIds) {
        $q->whereIn('contacts.company_id', $companyIds)
          ->orWhereHas('invoices', function ($subQuery) use ($companyIds) {
              $subQuery->whereIn('invoices.company_id', $companyIds);
          });
    });*/
}
	
	  /**
     * Relación con el modelo Cotizacion
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_id');
//	    return $this->hasMany(\App\Models\Cotizacion::class, 'contact_id'); 
    }

}
