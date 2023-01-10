<?php

namespace App\Providers;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'User' => User::class,
            'Role' => Role::class,
            'Permission' => Permission::class,
        ]);

        Gate::before(function (User $user) {
            return $user->hasRole(config('permission.superadmin_name')) ? true : null;
        });

        Model::preventLazyLoading(! app()->isProduction());
    }
}
