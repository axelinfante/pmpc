<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
	 protected $fillable = [
		'activo', 
	];
	
    protected $table = 'contact_groups';
}