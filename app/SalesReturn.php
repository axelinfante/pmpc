<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_return';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'return_date',
        'customer_id',
        'invoice_id',
        'account_id',
        'tax_id',
        'tax_amount',
        'product_total',
        'grand_total',
        'converted_total',
        'attachemnt',
        'note',
        'company_id'
    ];

    public function sales_return_items()
    {
        return $this->hasMany('App\SalesReturnItem',"sales_return_id");
    }

    public function customer()
    {
        return $this->belongsTo('App\Contact',"customer_id")->withDefault();
    }
	
	public function account()
    {
        return $this->belongsTo('App\Account',"account_id")->withDefault();
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax',"tax_id")->withDefault();
    }
	
	
    public function venta()
    {
        return $this->hasOne('App\Invoice',"id",'invoice_id');
    }

}