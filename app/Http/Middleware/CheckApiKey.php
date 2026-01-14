<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return JsonResponse|RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('x-api-key') !== config('api.key')) {
            return response()->json([
                'status'  => false,
                'code'    => 401,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
