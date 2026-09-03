<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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

        Gate::before(function (User $user) {
            $superAdmins = [
                'test@test.com'];

            if (in_array($user->email, $superAdmins, true)) {
                return true;
            }

            if (isset($user->user_type) && strtolower($user->user_type) === 'super_admin') {
                return true;
            }

            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return true;
            }

            return null; 
        });

        $this->registerGates();
    }

    /**
     * Registra dinámicamente las Gates asociadas a la tabla de permisos.
     */
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