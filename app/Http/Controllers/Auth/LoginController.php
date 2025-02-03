<?php

namespace App\Http\Controllers\Auth;

use App\Models\Aplikasi\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController
{
    /**
     * @return RedirectResponse|View
     */
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $request->validate([
            'user' => ['required', 'string', 'max:20'],
            'pass' => ['required', 'string', 'max:20'],
        ], $request->only(['user', 'pass']));

        $timebox = new Timebox;

        $user = $timebox->call(function (Timebox $t) use ($request): ?User {
            $user = User::query()
                ->whereRaw('AES_DECRYPT(id_user, ?) = ?', [config('khanza.app.userkey'), $request->get('user')])
                ->whereRaw('AES_DECRYPT(password, ?) = ?', [config('khanza.app.passkey'), $request->get('pass')])
                ->first();

            if ($user) {
                $t->returnEarly();
            }

            return $user;
        }, 250);

        if (! $user) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'user' => 'Username atau Password salah',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 3)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'user' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate($request->ip() ?? '');
    }
}
