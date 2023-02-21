<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('inarray', fn (mixed $needle, array $haystack, bool $strict = false) => in_array($needle, $haystack, $strict));

        Blade::if('null', fn ($expr) => is_null($expr));

        Blade::if('notnull', fn ($expr) => !is_null($expr));
    }
}
