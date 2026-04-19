<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento_car extends Model
{
    use HasFactory;

    protected $fillable = [
        'idVendedorMotor',
        'idCar',
        'motor_vendido_reservado',
        'ubicacion',
    ];

    public function car()
    {
        return $this->belongsTo('App\Car','idCar','id');
    }
}
