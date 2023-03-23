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
        'selected',
        'checked',
        'required',
    ];

    /**
     * HTML attributes that can be toggled with value
     * 
     * @var array<int, array<string, string>|string>
     */
    protected $booleanValuedAttributes = [
        'autocomplete' => ['on', 'off'],
        ''
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
