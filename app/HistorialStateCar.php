<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialStateCar extends Model
{
    use HasFactory;

    protected $table = 'historial_state_cars';	


    public function estadoAnt()
    {
        return $this->hasOne('App\Estado','id_current_state','id');
    }

    public function estadoNew()
    {
        return $this->hasOne('App\Estado','id_current_state','id');
    }

    public function user()
    {
        return $this->belongsTo('App\user',"id_user")->withDefault();
    }

    
    public function vehiculo()
    {
        return $this->belongsTo('App\Cars', "idCar")->withDefault();
    }

    public function oldState()
    {
        return $this->hasOne('App\Estado','id','id_current_state')->withDefault();
    }
    public function newState()
    {
        return $this->hasOne('App\Estado','id','id_new_state')->withDefault();
    }

}
