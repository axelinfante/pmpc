<?php

namespace App\Http\Controllers\Install;

use App\Utilities\Installer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;


class UpdateController extends Controller
{	
	public function __construct()
    {	
		
    }
	
	public function update_migration(){
		Artisan::call('migrate', ['--force' => true]);
		Installer::updateEnv([
            'APP_VERSION' =>  '2.3',
        ]);
		echo "Migration Updated Sucessfully";
	} 
}
