<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointVehiculo extends Model
{
    use HasFactory;
    protected $table = 'vehiculos_checkpoints';


    protected $fillable = [
        'id',
        'checkpoint_id',
        'vehiculo_id',
        'start_date',
        'end_date',
        'status',
        'status_date',
        'observaciones',
        'user_id',
    ];

    public function checkpoint()
    {
        return $this->belongsTo('App\Checkpoint', 'checkpoint_id', 'id');
    }
}
