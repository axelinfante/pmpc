<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    /**
    * @var array
    */
    protected $activoTypes = [
        'Si',
        'No'
    ];

    /**
    * @param int $value
    * @return string|null
    */
    public function getActivoTypeAttribute($value)
    {
        return Arr::get($this->activoTypes, $value);
    }
}
