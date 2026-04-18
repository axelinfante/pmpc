<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenDespacho extends Model
{
    use HasFactory;
    protected $table = 'ordenes_despacho';

	protected $fillable = [
        'invoice_id','invoiceitem_id','description','quantity','company_id','estatus',
        // Add other fillable attributes here (e.g., 'name', 'amount', 'status')
    ];

    public function cotizacion()
    {
        return $this->hasOne('App\Invoice',"id",'invoice_id');
    }

	
	public function invoice_items()
    {
        return $this->hasOne('App\InvoiceItem',"product_id","invoiceitem_id");
    }
	



	     public function itemInvoice()
    {
        return $this->hasOne('App\InvoiceItem',"id",'invoiceitem_id');
    }

	
	 public function producto()
    {
        return $this->hasOne('App\Product','id','pieza');
    }

 /*   public function transaction()
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
        }*/
	
	
	/*
	 public function producto()
    {
        return $this->hasOne('App\Product','id','pieza');
    }
    public function car()
    {
        return $this->hasOne('App\Cars',"id",'idCar');
    }
    

    public function venta()
    {
        return $this->hasOne('App\Invoice',"id",'id_venta');
    }

    public function marcaModelo()
    {
        return $this->belongsTo('App\MarcaModelo','marca_modelo','id');
    }

    public function lugares()
    {
        return $this->hasOne('App\Lugar_entregas',"id",'ubicacion')->withDefault();
    }
	*/
	
}
