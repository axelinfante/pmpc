<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
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