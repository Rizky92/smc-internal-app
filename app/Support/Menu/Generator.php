<?php

namespace App\Support\Menu;

use Illuminate\Support\Facades\App;
use Route;

class Generator
{
    /** @var \Illuminate\Foundation\Application $app */
    protected $app;

    /** @var string $path */
    protected $path;

    /** @var string $breadcrumbSeparator = '/' */
    protected $breadcrumbSeparator = '/';

    /** @var string $bladeComponentNamespace = 'sidebar' */
    protected $bladeComponentNamespace = 'sidebar';

    /** @var array $basePath */
    protected $basePath = [
        'title' => 'Dashboard',
        'route' => [
            'name' => 'admin.dashboard',
            'verb' => 'GET',
            'middleware' => null,
        ],
        'icon' => 'fas fa-home',
        'permissions' => true,
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getRoutes()
    {
        return Route::getRoutes()->getRoutes();
    }
}