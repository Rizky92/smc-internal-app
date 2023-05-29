<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController
{
    public function __invoke(Request $request): View
    {
        return view('home');
    }
}
