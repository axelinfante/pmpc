<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableTransfer;

class Transfer extends Model implements AuditableTransfer
{
    use HasFactory, Auditable;
	
	protected $fillable = [
		'fecha_traslado',
		'detalles',
	    'status',  
        'user_id', 
		'almacen_origen_id',
		'almacen_destino_id'
    ];
	
	protected $casts = [
        'fecha_traslado' => 'date', 
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];
	
	
	public function getFechaTrasladoFormattedAttribute()
	{
		return $this->fecha_traslado->format('d/m/Y'); // Formato deseado
	}
	
	 public function getFechaTrasladoAttribute($value) {
        return Carbon::parse($value)->format('d M, Y');
    }

    public function TransfersProduct() {
        return $this->hasMany(TransfersProduct::class, 'transfers_id', 'id');
    }
	
    public function almacen_origen()
	{
		return $this->belongsTo(Lugar_entregas::class, 'almacen_origen_id');
	}

	public function almacen_destino()
	{
		return $this->belongsTo(Lugar_entregas::class, 'almacen_destino_id');
	}
	
	 public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	
	public static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $number = Transfer::max('id') + 1;
			$padded_text = 'TRA' . '-' . str_pad($number, 10, 0, STR_PAD_LEFT);
            $model->reference = $padded_text;
			
			
        });
    }

}
