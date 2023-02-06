<?php

namespace App\Providers;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Str;

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

        Collection::macro('takeEach', function ($key) {
            /** @var \Illuminate\Support\Collection $this */

            if (Arr::isAssoc($this->all())) {
                throw new Exception("Collection must be an array list, array associative found.");
            }

            return $this->map(fn ($value) => $value[$key]);
        });
    }
}
