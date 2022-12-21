<?php

namespace App\Http\Controllers\Auth;

use App\Models\Aplikasi\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user' => ['required', 'string', 'max:20'],
            'pass' => ['required', 'string', 'max:20'],
        ], $request->only(['user', 'pass']));

        $user = User::query()
            ->whereRaw('AES_DECRYPT(id_user, "nur") = ?', $request->get('user'))
            ->whereRaw('AES_DECRYPT(password, "windi") = ?', $request->get('pass'))
            ->where('status', '1')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'username atau password salah'
            ]);
        }
        
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->intended('admin.dashboard');

    }
}
