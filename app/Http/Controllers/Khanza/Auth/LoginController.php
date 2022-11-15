<?php

namespace App\Http\Controllers\Khanza\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     *
     * @return \Illuminate\Http\Response|\Illuminate\Support\Facades\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->validate([
            'username' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:20'],
        ], $request->only(['username', 'password']));

        $user = User::selectRaw('*')
            ->whereRaw('AES_DECRYPT(id_user, "nur") = ?', $request->get('username'))
            ->whereRaw('AES_DECRYPT(password, "windi") = ?', $request->get('password'))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah'
            ]);
        }

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
