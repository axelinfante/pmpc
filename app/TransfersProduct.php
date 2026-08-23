<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Transfer;
use App\Product;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableTransfersProduct;

class TransfersProduct extends Model implements AuditableTransfersProduct
{
    use HasFactory, Auditable;

    protected $guarded = [];

//    protected $with = ['product'];

    public function transfer() {
        return $this->belongsTo(Transfer::class, 'transfers_id', 'id');
    }

    public function inventario() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
	
}
