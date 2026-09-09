<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInvoice;

class Invoice extends Model implements AuditableInvoice
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use Auditable;
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
	
	
	public function salesReturns()
	{
		return $this->hasMany(SalesReturn::class, 'invoice_id');
	}

	public function payments()
	 {
			return $this->hasMany(Transaction::class, 'invoice_id')
				->where('type', 'income')
				->where('dr_cr', 'cr');
	}

	public function retiros_cliente()
	{
		return $this->hasMany(Transaction::class, 'invoice_id')
			->where('type', 'expense')
			->where('dr_cr', 'dr');
	}
	
	public function retiros_cliente_origen()
	{
		//return $this->hasMany(Transaction::class, 'transaccion_revertida_id');
		return $this->hasMany(Transaction::class, 'transaccion_revertida_id')
			->where('type', 'income')
			->where('dr_cr', 'cr');
	}
	
	public static function boot() {
    parent::boot();

    static::creating(function ($model) {
        // Envolvemos todo en una transacción para que el bloqueo funcione correctamente
        \DB::transaction(function () use ($model) {
            
            $maxNumber = \DB::table('company_settings')
                ->where('name', 'invoice_starting')
                ->lockForUpdate() 
                ->value('value'); 

            if ($maxNumber) {
                $number = (int)$maxNumber;
            } else {
                $number = 1;
            }
            $model->invoice_number = $number;

            \DB::table('company_settings')
                ->where('name', 'invoice_starting')
                // ->where('company_id', company_id()) 
                ->update(['value' => $number + 1]); 
        });
    });
}
	
	
}