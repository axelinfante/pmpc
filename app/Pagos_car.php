<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos_car extends Model
{
    use HasFactory;

    public function transaction()
    {
        return $this->hasMany('App\Transaction','id','id_gasto');
    }


    public function vehiculo()
    {
        return $this->belongsTo('App\Cars', "id_car")->withDefault();
    }
}
