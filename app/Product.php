<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableTable;

class Product extends Model implements AuditableTable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    use Auditable;

    protected $table = 'products';

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo('App\Supplier', "supplier_id")->withDefault();
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax', "tax_id")->withDefault();
    }
    public function category()
    {
        return $this->hasMany('App\Categoria_product', "product_id", 'id');
    }
    public function item()
    {
        return $this->belongsTo('App\Item', "item_id")->withDefault();
    }

    public function stock()
    {
        return $this->hasOne('App\Stock', "product_id")->withDefault();
    }
    public function marcaModelo()
    {
        return $this->belongsTo('App\MarcaModelo', 'marca_modelo', 'id');
    }
    public function vehiculo()
    {
        return $this->belongsTo('App\Cars', 'nro_interno', 'id');
    }
    public function img()
    {
        return $this->hasMany('App\Imagen', 'idProduct', 'id');
    }

    public function deposito()
    {
        return $this->hasOne('App\Lugar_entregas', 'id', 'idDeposito')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo('App\User', "user_id")->withDefault();
    }

    public function invoiceItems()
    {
        return $this->hasMany('App\InvoiceItem', 'product_id', 'id');
    }
}
