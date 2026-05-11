<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableQuotationItem;

class QuotationItem extends Model implements AuditableQuotationItem
{
    /**
     * The table associated with the model.
     *
     * @var string
     */

     use Auditable;
    protected $table = 'quotation_items';

    public function item()
    {
        return $this->belongsTo('App\Item',"item_id")->withDefault();
    }

    public function product()
    {
        return $this->belongsTo('App\Product',"product_id")->withDefault();
    }
	
	public function taxes()
    {
        return $this->hasMany('App\QuotationItemTax',"quotation_item_id");
    }

}