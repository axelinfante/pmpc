<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemTax extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_item_taxes';
	
	protected $fillable = [
        'invoice_id','invoice_item_id','tax_id','name','company_id','amount',
        // Add other fillable attributes here (e.g., 'name', 'amount', 'status')
    ];

    public function invoice_item()
    {
        return $this->belongsTo('App\InvoiceItem',"invoice_item_id")->withDefault();
    }

}