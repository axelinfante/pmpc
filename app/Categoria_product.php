<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria_product extends Model
{
    use HasFactory;

    public function categoria()
    {
        return $this->belongsTo('App\Categoria',"categoria_id",'id')->withDefault();
    }
}
