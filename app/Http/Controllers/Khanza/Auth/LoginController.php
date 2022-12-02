<?php

namespace App\Http\Controllers\Khanza\Auth;

use App\Admin;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
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
     * Store user session if credential matches in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user' => ['required', 'string', 'max:20'],
            'pass' => ['required', 'string', 'max:20'],
        ], $request->only(['user', 'pass']));

        // $admin = Admin::selectRaw('*')
        //     ->whereRaw('AES_DECRYPT(usere, "nur") = ?', $request->get('user'))
        //     ->whereRaw('AES_DECRYPT(passworde, "windi") = ?', $request->get('pass'))
        //     ->first();

        $user = User::selectRaw('*')
            ->whereRaw('AES_DECRYPT(id_user, "nur") = ?', $request->get('user'))
            ->whereRaw('AES_DECRYPT(password, "windi") = ?', $request->get('pass'))
            ->where('status', '1')
            ->first();

        // if ($admin) {
        //     dump($admin, $admin instanceof Authenticatable);

        //     Auth::guard('admin')->login($admin);

        //     $request->session()->regenerate();

        //     dd(Auth::check());

        //     return redirect()->route('admin.dashboard');
        // }

        if ($user) {
            Auth::guard('web')->login($user);

            $request->session()->regenerate();

            // dd(Auth::check());

            return redirect()->route('admin.dashboard');
        }

        throw ValidationException::withMessages([
            'user' => 'username atau password salah'
        ]);
    }
}
