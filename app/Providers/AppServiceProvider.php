<?php

namespace App\Providers;

use App\Database\Eloquent\Model;
use App\Database\Query\Grammars\MysqlGrammar;
use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;
use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;
use App\Domain\Akreditasi\Repositories\AssessmentScoreHistoryRepositoryInterface;
use App\Domain\Akreditasi\Repositories\AssessmentScoreRepositoryInterface;
use App\Domain\Akreditasi\Repositories\FocusAreaRepositoryInterface;
use App\Domain\Akreditasi\Repositories\ProofMethodRepositoryInterface;
use App\Domain\Akreditasi\Repositories\StandardRepositoryInterface;
use App\Infrastructure\Akreditasi\Repositories\EloquentAssessmentDocumentRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentAssessmentElementRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentAssessmentScoreHistoryRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentAssessmentScoreRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentFocusAreaRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentProofMethodRepository;
use App\Infrastructure\Akreditasi\Repositories\EloquentStandardRepository;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\MixinArr;
use App\Support\MixinCollections;
use App\Support\MixinEloquentBuilder;
use App\Support\MixinQueryBuilder;
use App\Support\MixinStr;
use App\Support\MixinStringable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string[]|class-string>
     */
    protected $mixins = [
        Arr::class             => MixinArr::class,
        Collection::class      => MixinCollections::class,
        Str::class             => MixinStr::class,
        Stringable::class      => MixinStringable::class,
        QueryBuilder::class    => MixinQueryBuilder::class,
        EloquentBuilder::class => MixinEloquentBuilder::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepositoryBindings();
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

        $this->registerBladeDirectives();
        $this->registerModelConfigurations();
        $this->registerSuperadminRole();
        $this->registerMacrosAndMixins();
    }

    /**
     * Centralized Interface to Implementation bindings.
     */
    protected function registerRepositoryBindings(): void
    {
        $this->app->bind(
            FocusAreaRepositoryInterface::class,
            EloquentFocusAreaRepository::class
        );

        $this->app->bind(
            StandardRepositoryInterface::class,
            EloquentStandardRepository::class
        );

        $this->app->bind(
            ProofMethodRepositoryInterface::class,
            EloquentProofMethodRepository::class
        );

        $this->app->bind(
            AssessmentElementRepositoryInterface::class,
            EloquentAssessmentElementRepository::class
        );

        $this->app->bind(
            AssessmentDocumentRepositoryInterface::class,
            EloquentAssessmentDocumentRepository::class
        );

        $this->app->bind(
            AssessmentScoreRepositoryInterface::class,
            EloquentAssessmentScoreRepository::class
        );

        $this->app->bind(
            AssessmentScoreHistoryRepositoryInterface::class,
            EloquentAssessmentScoreHistoryRepository::class
        );
    }

    public function registerBladeDirectives(): void
    {
        Blade::if('inarray', fn ($needle, array $haystack, bool $strict = false): bool => in_array($needle, $haystack, $strict));

        Blade::if('null', fn ($expr): bool => is_null($expr));

        Blade::if('notnull', fn ($expr): bool => ! is_null($expr));
    }

    public function registerModelConfigurations(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

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

    public function registerMacrosAndMixins(): void
    {
        foreach ($this->mixins as $class => $mixins) {
            if (! in_array('mixin', get_class_methods($class), $strict = true)) {
                continue;
            }

            if (is_string($mixins)) {
                $class::mixin(new $mixins);

                continue;
            }

            foreach ($mixins as $mixin) {
                $class::mixin(new $mixin);
            }
        }
    }
}
