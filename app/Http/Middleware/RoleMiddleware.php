<?php

namespace App\Http\Middleware;

use App\Models\Aplikasi\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  array|string  $role
     * @param  string  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $guard = null)
    {
        /** @var User */
        $user = Auth::guard($guard)->user();

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        // Throw 404 error to hide menu existence from unauthorized users.
        if (! $user->hasAnyRole($roles)) {
            abort(404);
        }

        return $next($request);
    }
}
