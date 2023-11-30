<?php

namespace App\Providers;

use App\Database\Eloquent\Model;
use App\Database\Query\Grammars\MysqlGrammar;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
        //
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

        /** @psalm-scope-this Illuminate\Database\Eloquent\Builder */
        Builder::macro('orderByField', function ($column, $values, $direction = 'asc') {
            $binds = [];

            for ($i = 0; $i < count($values); $i++) {
                $binds[] = '?';
            }

            $binds = implode(', ', $binds);

            if ($column instanceof Expression) {
                $column = $column->getValue();
            }

            $direction = strtolower($direction);

            if (! in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
            }

            $startsWith = sprintf('field(%s, ', $column);
            $endsWith = sprintf(') %s', $direction);

            return $this->orderByRaw(Str::wrap($binds, $startsWith, $endsWith), $values);
        });

        $this->registerBladeDirectives();
        $this->registerModelConfigurations();
        $this->registerSuperadminRole();
        $this->registerCollectionMacrosAndMixins();
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
}
