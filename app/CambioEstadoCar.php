<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CambioEstadoCar extends Model
{
    protected $table = 'cambio_estado_cars';

    protected $primaryKey = 'id_estado';

    public $timestamps = false; 
}