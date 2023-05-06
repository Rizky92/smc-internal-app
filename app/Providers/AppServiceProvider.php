<?php

namespace App\Providers;

use App\Database\Query\Grammars\MysqlGrammar;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $mixins = [
        \Illuminate\Support\Collection::class => \App\Support\Mixins\CustomCollections::class,
        \Illuminate\Support\Str::class => \App\Support\Mixins\CustomStr::class,
        \Illuminate\Support\Stringable::class => \App\Support\Mixins\CustomStringable::class,
    ];

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
        // Gunakan custom grammar untuk mysql agar bisa
        // melakukan pencatatan timestamp dengan presisi tingkat 6
        // https://carbon.nesbot.com/laravel/
        DB::connection('mysql_smc')->setQueryGrammar(new MysqlGrammar);

        $this->registerBladeDirectives();
        $this->registerModelConfigurations();
        $this->registerSuperadminRole();
        $this->registerCollectionMacrosAndMixins();
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

    public function registerCollectionMacrosAndMixins()
    {
        foreach ($this->mixins as $class => $mixins) {
            if (! in_array('mixin', get_class_methods($class), $strict = true)) {
                continue;
            }

            if (is_string($mixins)) {
                $class::mixin(new $mixins);

                continue;
            }
    
            foreach ($mixins as $mixinClass) {
                $class::mixin(new $mixinClass);
            }
        }
    }
}
