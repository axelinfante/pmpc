<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';
    
	public function invoice_items()
    {
        return $this->hasMany('App\InvoiceItem',"invoice_id");
    }

    public function transaction()
    {
        return $this->hasMany('App\Transaction','invoice_id','id' );
    }
    public function comision()
    {
        return $this->hasOne('App\Comision','id_venta',"id")->withDefault();
    }
    public function client()
    {
        return $this->belongsTo('App\Contact',"client_id")->withDefault();
    }

    public function project()
    {
        return $this->belongsTo('App\Project',"related_id")->withDefault();
    }

    public function company()
    {
        return $this->belongsTo('App\Company',"company_id")->withDefault();
    }
	
	public function taxes()
    {
        return $this->hasMany('App\InvoiceItemTax',"invoice_id");
    }

    public function vendedor()
    {
        return $this->hasOne('App\User','id',"user_id")->withDefault();
    }

    public function devoluciones()
    {
         return $this->hasMany(ProductReturn::class);
    }
	
	 public function retiros()
    {
        return $this->hasMany('App\Transaction','transaccion_revertida_id','id' );
    }
	

}