<?php

namespace App\Support\Menu;

use Illuminate\Support\Collection;
use Route;

class Generator
{
    protected ?Collection $menu;

    protected ?Collection $routes;
    
    public function __construct()
    {
        $this->routes = collect(Route::getRoutes()->getRoutes());
    }

    public static function make(): void
    {
        //
    }

    public function fromRoute(string $name): void
    {
        if ($this->menuExists($name)) {
            
        }
    }

    protected function menuExists(string $name): bool
    {
        return $this->menu->has($name);
    }
}
