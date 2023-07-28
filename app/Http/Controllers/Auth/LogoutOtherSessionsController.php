<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogoutOtherSessionsController
{
    public function show(): View
    {
        return view('auth.logout-other-session');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logoutOtherDevices($request->getPassword());

        return redirect()->route('auth.logout-other-session');
    }
}
