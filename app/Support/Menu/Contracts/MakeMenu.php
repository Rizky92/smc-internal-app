<?php

namespace App\Support\Menu\Contracts;

use Illuminate\View\View;

interface MakeMenu
{
    public static function make();

    public function render(): View;
}