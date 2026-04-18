<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chart_of_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'company_id',
    ];
}