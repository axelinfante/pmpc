<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial_product extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo('App\Item',"item_id")->withDefault();
    }

    public function user()
    {
        return $this->belongsTo('App\user',"user_id")->withDefault();
    }

    public function marcaModelo()
    {
        return $this->belongsTo('App\MarcaModelo','marca_modelo','id');
    }

    public function img()
    {
        return $this->hasMany('App\Imagen','idProduct','product_id');
    }

    public function deposito()
    {
        return $this->hasOne('App\Lugar_entregas','id','idDeposito')->withDefault();
    }
}
