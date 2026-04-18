<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $table = 'comisiones';

    public function vendedor()
    {
        return $this->hasOne('App\User','id',"id_vendedor")->withDefault();
    }

    public function invoice()
    {
        return $this->hasOne('App\Invoice','id',"id_venta")->withDefault();
    }

    public function gasto()
    {
        return $this->hasOne('App\Transaction','id_comision',"id")->withDefault();
    }
}
