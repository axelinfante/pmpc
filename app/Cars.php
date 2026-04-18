<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableTable;

class Cars extends Model implements AuditableTable
{
    use SoftDeletes;
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'id',
        'fecha_asignacion',
        'fecha_confirmacion_contacto',
        'fecha_ingreso',
        'tipo_baja',
        'idAseguradora',
        'idLugar_entrega',
        'idEstado',
        'observaciones_admin',
        'motor_nro',
        'dominio',
        'chasis',
        'idMarca_modelo',
        'company_id',
        'fecha_envio_baja',
        'fecha_envio_drnpa',
        'fecha_finalizacion',
        'estado_tramite',
        'titular',
        'no_drnpa',
        'tipo_vehiculo'
    ];

    public function tramitador()
    {
        return $this->belongsTo('App\User','idTramitador','id');
    }


    public function aseguradora()
    {
        return $this->belongsTo('App\Aseguradora','idAseguradora','id');
    }
    public function provincias()
    {
        return $this->belongsTo('App\Provincia','provincia','id');
    }
    public function lugar_entrega()
    {
        return $this->belongsTo('App\Lugar_entregas','idLugar_entrega','id');
    }

    public function marca_modelo()
    {
        return $this->belongsTo('App\MarcaModelo','idMarca_modelo','id');
    }
    public function estado()
    {
        return $this->belongsTo('App\Estado','idEstado','id');
    }
    public function responsable_retiro()
    {
        return $this->belongsTo('App\User','idResponsable_retiro','id');
    }
    public function pieza()
    {
        return $this->hasMany('App\Product','car_id','id');
    }
    public function img()
    {
        return $this->hasMany('App\Imagen','idCar','id')->whereNull('seccion');;
    }

    public function img_recepcion()
    {
        return $this->hasMany('App\Imagen','idCar','id') ->where('seccion', 'receptor');
    }

    public function pieza_ausente()
    {
        return $this->hasMany('App\Pieza_ausente','id_car','id');
    }
    public function seguimiento()
    {
        return $this->hasOne('App\Seguimiento_car','idCar','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Company','company_id','id');
    }

    public function checkpoints()
    {
        return $this->hasMany('App\CheckpointVehiculo','vehiculo_id','id');
    }

    public function str_interno()
    {
        if ($this->company_id == 1) {
           return 'PM-'.$this->id;
        } else if ($this->company_id == 2) {
            return 'PC-'.$this->id;
        }
    }
	
	 protected $casts = [
        'properties' => 'array', 
       
    ];

	
}
