<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        return view('dashboard');
    }
}
