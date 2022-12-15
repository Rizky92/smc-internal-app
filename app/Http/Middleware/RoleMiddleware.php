<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, string $role, $guard = null)
    {
        /** @var \App\Models\Aplikas\User $user */
        $user = Auth::guard($guard)->user();

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if (! $user->user()->hasAnyRole($roles)) {
            abort(404);
        }

        return $next($request);
    }
}
