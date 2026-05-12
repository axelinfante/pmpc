<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInvoiceItem;

class InvoiceItem extends Model implements AuditableInvoiceItem
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

    use Auditable;
    protected $table = 'invoice_items';

    public function item()
    {
        return $this->belongsTo('App\Item',"item_id")->withDefault();
    }

    public function product()
    {
        return $this->belongsTo('App\Product',"product_id")->withDefault();
    }

    public function marcaModelo()
    {
        return $this->belongsTo('App\MarcaModelo','marca_modelo','id');
    }
	
	public function taxes()
    {
        return $this->hasMany('App\InvoiceItemTax',"invoice_item_id");
    }

}