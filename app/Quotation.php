<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableQuotation;

class Quotation extends Model implements AuditableQuotation
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use Auditable;
    protected $table = 'quotations';
    
	public function quotation_items()
    {
        return $this->hasMany('App\QuotationItem',"quotation_id");
    }

    public function client()
    {
        return $this->belongsTo('App\Contact',"related_id")->withDefault();
    }

    public function lead()
    {
        return $this->belongsTo('App\Lead',"related_id")->withDefault();
    }

    public function vendedor()
    {
        return $this->hasOne('App\User',"id", 'user_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo('App\Cars', "car_id")->withDefault();
    }
}