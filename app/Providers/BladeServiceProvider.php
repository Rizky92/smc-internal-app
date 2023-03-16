<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{

    /**
     * HTML attributes that can be toggled by adding the attribute
     * 
     * @var array<int, string>
     */
    protected $booleanAttributes = [
        //
    ];

    /**
     * HTML attributes that can be toggled with value
     * 
     * @var array<int, array<string, string>|string>
     */
    protected $booleanValuedAttributes = [
        //
    ];

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
        //
    }
}
