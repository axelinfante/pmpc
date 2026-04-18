<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anulados_comision extends Model
{
    use HasFactory;
    /*protected $table = 'invoice_items';*/

    protected $fillable = [
        'observaciones', 'estatus', 'monto_anulado','invoice_id'
        /*`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `invoiceitem_id` BIGINT(20) NOT NULL,
        `invoice_id` BIGINT(20) NOT NULL,
        `item_id` BIGINT(20) NOT NULL,
        `description` TEXT NULL DEFAULT NULL,
        `quantity` DECIMAL(10,2) NOT NULL,
        `unit_cost` DECIMAL(10,2) NOT NULL,
        `discount` DECIMAL(10,2) NOT NULL,
        `tax_method` VARCHAR(10) NULL DEFAULT NULL,
        `tax_id` BIGINT(20) NULL DEFAULT NULL,
        `tax_amount` DECIMAL(10,2) NULL DEFAULT NULL,
        `sub_total` DECIMAL(10,2) NOT NULL,
        `company_id` BIGINT(20) NOT NULL,
        `idCar` BIGINT(20) NULL DEFAULT NULL,
        `product_id` BIGINT(20) NOT NULL,
        `observaciones` TEXT NULL DEFAULT NULL,
        `estatus` varchar(100) NOT NULL,
        `monto_anulado` DECIMAL(10,2) NOT NULL,*/



    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo('App\Product',"product_id")->withDefault();
    }

    public function marcaModelo()
    {
        return $this->belongsTo('App\MarcaModelo','marca_modelo','id');
    }

}
