<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_desarme extends Model
{
    use HasFactory;
    protected $table = 'ordenes_desarme';

    public function producto()
    {
        return $this->hasOne('App\Product','id','product_id');
    }
    public function car()
    {
        return $this->hasOne('App\Cars',"id",'idCar');
    }
    public function cotizacion()
    {
        return $this->hasOne('App\Quotation',"id",'id_cotizacion');
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
	
	 public function item()
    {
        return $this->belongsTo('App\Item', "pieza")->withDefault();
    }
}
