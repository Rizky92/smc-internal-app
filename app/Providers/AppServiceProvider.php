<?php

namespace App\Providers;

use App\Database\Eloquent\Model;
use App\Database\Query\Grammars\MysqlGrammar;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Rules\DateBetween;
use App\Rules\DoesntExist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string[]|class-string>
     */
    protected $mixins = [
        \Illuminate\Support\Arr::class        => \App\Support\MixinArr::class,
        \Illuminate\Support\Collection::class => \App\Support\MixinCollections::class,
        \Illuminate\Support\Str::class        => \App\Support\MixinStr::class,
        \Illuminate\Support\Stringable::class => \App\Support\MixinStringable::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (
            $this->app->environment('local') &&
            class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)
        ) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Menggunakan custom grammar untuk driver db mysql agar bisa
        // melakukan pencatatan timestamp dengan presisi tingkat 6
        // https://carbon.nesbot.com/laravel/
        DB::connection('mysql_smc')->setQueryGrammar(new MysqlGrammar);

        /** @psalm-scope-this Illuminate\Database\Eloquent\Builder */
        Builder::macro('isEagerLoaded', fn (string $name): bool => isset($this->eagerLoad[$name]));

        $this->registerBladeDirectives();
        $this->registerModelConfigurations();
        $this->registerSuperadminRole();
        $this->registerCollectionMacrosAndMixins();
        $this->registerValidationRules();
    }

    public function registerBladeDirectives(): void
    {
        Blade::if('inarray', fn ($needle, array $haystack, bool $strict = false): bool => in_array($needle, $haystack, $strict));

        Blade::if('null', fn ($expr): bool => is_null($expr));

        Blade::if('notnull', fn ($expr): bool => !is_null($expr));
    }

    public function registerModelConfigurations(): void
    {
        Model::preventLazyLoading(!app()->isProduction());

        Relation::morphMap([
            'User'       => User::class,
            'Role'       => Role::class,
            'Permission' => Permission::class,
        ]);
    }

    public function registerSuperadminRole(): void
    {
        Gate::before(fn (User $user) => $user->hasRole(config('permission.superadmin_name')) ?: null);
    }

    public function registerCollectionMacrosAndMixins(): void
    {
        foreach ($this->mixins as $class => $mixins) {
            if (!in_array('mixin', get_class_methods($class), $strict = true)) {
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

    public function registerValidationRules(): void
    {
        Rule::macro('doesntExists', fn (string $model, string $column) => new DoesntExist($model, $column));
        Rule::macro('dateBetween', fn ($start, $end) => new DateBetween($start, $end));
    }
}
