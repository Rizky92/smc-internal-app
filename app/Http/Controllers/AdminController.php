<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $isUserLoggedIn = auth()->check();
        $username = optional(auth()->user())->name;
        $email = optional(auth()->user())->email;

        return view('admin.dashboard', compact('isUserLoggedIn', 'username', 'email'));
    }
}
