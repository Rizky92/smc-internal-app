<?php

namespace App\Http\Controllers\Auth;

use App\Models\Aplikasi\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user' => ['required', 'string', 'max:20'],
            'pass' => ['required', 'string', 'max:20'],
        ], $request->only(['user', 'pass']));

        $user = User::query()
            ->whereRaw('AES_DECRYPT(id_user, ?) = ?', [config('khanza.app.userkey'), $request->get('user')])
            ->whereRaw('AES_DECRYPT(password, ?) = ?', [config('khanza.app.passkey'), $request->get('pass')])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'username atau password salah'
            ]);
        }
        
        Auth::guard('web')
            ->login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
