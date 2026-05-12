<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;
    protected $fillable = [
        'modelo',
    ];
	
	public function marcas()
{
    return $this->belongsToMany(
        Marca::class, 
        'marca_modelos', 
        'idModelo', 
        'idMarca'
    )->withTimestamps();
	}
}
