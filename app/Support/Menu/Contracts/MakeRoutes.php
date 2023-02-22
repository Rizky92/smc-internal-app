<?php

namespace App\Support\Menu\Contracts;

use Illuminate\Routing\RouteCollection;

interface MakeRoutes
{
    public function routeList(): RouteCollection;

    public function fromRoute(string $name);

    public function getParent(string $name = null);

    public function getChildrens(string $name = null): RouteCollection;
}