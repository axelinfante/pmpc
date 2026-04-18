<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaModelo extends Model
{
    use HasFactory;

    protected $fillable = [
        'idMarca',
        'idModelo'
    ];

    public function marca()
    {
        return $this->belongsTo('App\Marca','idMarca','id');
    }
    public function modelo()
    {
        return $this->belongsTo('App\Modelo','idModelo','id');
    }
}
