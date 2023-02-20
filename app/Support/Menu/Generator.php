<?php

namespace App\Support\Menu;

class Generator
{
    protected $path;

    protected $breadcrumbSeparator = '/';

    protected $bladeComponentNamespace = 'sidebar';

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

    public function __construct()
    {
        
    }
}