<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Import the trait

class Puesto extends Model
{
	 use HasFactory;
	 
	 protected $fillable = [
        'puesto','predeterminada','user_id', 'activo','company_id'
    ];
	 
	 public function asignado() {
		return $this->belongsTo('App\User', 'user_id')->withDefault();
	}
	
	public function company()
    {
        return $this->belongsTo('App\Company','company_id')->withDefault();
    }
	
	
/*	public function usuario() {
		return $this->belongsTo('App\User', 'user_id')->withDefault();
	}
*/
	
}
