<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products_returns';

    public function producto()
    {
       // return $this->hasOne('App\Product');
        return $this->belongsTo('App\Product',"product_id")->withDefault();
    }

    public function item()
    {
       // return $this->hasOne('App\Product');
        return $this->belongsTo('App\Item',"product_id")->withDefault();
    }


    

    public function invoice()
    {
        return $this->belongsTo('App\Invoice',"invoice_id")->withDefault();
    }
    public function company()
    {
        return $this->belongsTo('App\Company',"company_id")->withDefault();
    }

}