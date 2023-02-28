<?php

namespace App\Providers;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Menu\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
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
        $this->registerBladeDirectives();
        $this->registerModelConfigurations();
        $this->registerMorphRelations();
        $this->registerSuperadminRole();
        $this->registerCollectionMacros();
        $this->registerQueryBuilderMacros();
        $this->registerMenuProvider();
    }

    public function registerBladeDirectives()
    {
        Blade::if('inarray', fn (mixed $needle, array $haystack, bool $strict = false) => in_array($needle, $haystack, $strict));

        Blade::if('null', fn ($expr) => is_null($expr));

        Blade::if('notnull', fn ($expr) => !is_null($expr));
    }

    public function registerModelConfigurations()
    {
        Model::preventLazyLoading(! app()->isProduction());
    }

    public function registerMorphRelations()
    {
        Relation::morphMap([
            'User' => User::class,
            'Role' => Role::class,
            'Permission' => Permission::class,
        ]);
    }

    public function registerSuperadminRole()
    {
        Gate::before(function (User $user) {
            return $user->hasRole(config('permission.superadmin_name')) ? true : null;
        });
    }

    public function registerCollectionMacros()
    {
        Collection::macro('whereLike', function ($search, $looseRange = 1) {
            /** @var \Illuminate\Support\Collection $this */

            return $this->filter(function ($v) use ($search, $looseRange) {
                return levenshtein($v, $search) <= $looseRange;
            });
        });

        Collection::macro('containsLike', function ($search = '', $looseRange = 1) {
            /** @var \Illuminate\Support\Collection $this */

            return $this->contains(function ($v) use ($search, $looseRange) {
                if (empty($search)) return false;
                
                return levenshtein($v, $search) <= $looseRange;
            });
        });
    }

    public function registerMenuProvider()
    {
        $this->app->bind('menu', fn ($app) => new Generator($app));
    }

    public function registerQueryBuilderMacros()
    {
        //
    }
}
