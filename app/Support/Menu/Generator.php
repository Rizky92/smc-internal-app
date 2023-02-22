<?php

namespace App\Support\Menu;

use App\Support\Menu\Contracts\MakeBreadcrumbs;
use App\Support\Menu\Contracts\MakeMenu;
use App\Support\Menu\Contracts\MakeRoutes;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Generator
{
    protected ?Collection $collection = null;

    public function __construct() {
    }

    public static function make()
    {
        
    }

    public function fromRoute(string $name)
    {
        
    }
}