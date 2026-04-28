<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
//use App\Permission;
use App\Permission;
use App\User;



class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
		$this->registerGates();
		
		Gate::before(function (User $user) {
			$usuarios= array("test@test.com", "crismartinez@pmpc.com.ar");
			//return (in_array($user->email, $usuarios)) $user->email=='test@test.com' ? true : null;
			return (in_array($user->email, $usuarios)) ? true : null;
			  //return $user->user_type=='user' ? true : null;
			//user_type
			//email
				//return $user->hasRole(''); // or create an  `isSuperAdmin(...)` function in `User` model
			});

    }
	
	protected function registerGates(): void
    {
        try {
            foreach (Permission::pluck('permission') as $permission) {
                Gate::define($permission, function ($user) use ($permission) {
                    return $user->hasPermission($permission);
                });
            }
        } catch (\Exception $e) {
            info('registerPermissions(): Database not found or not yet migrated. Ignoring user permissions while booting app.');
        }
    }
}
