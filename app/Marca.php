<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \DateTimeInterface;

class Marca extends Model
{
    use HasFactory;

	protected $dates = [
        'created_at',
        'updated_at',
    ];
	
    protected $fillable = [
        'marca',
        'activo',
		'created_at',
        'updated_at',
    ];
	
	 protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
	
	public function modelos()
	{
		//return $this->belongsToMany(Ingredient::class)->withPivot('amount');
    return $this->belongsToMany(
        Modelo::class, 
        'marca_modelos', // Nombre de la tabla pivote
        'idMarca',       // Clave foránea de Marca en la pivote
        'idModelo'       // Clave foránea de Modelo en la pivote
    )->withTimestamps(); // Para que Laravel gestione created_at y updated_at
	}
}