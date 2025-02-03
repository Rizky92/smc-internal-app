<?php

namespace App\Http\Middleware;

use App\Database\Eloquent\Model;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AuthorizeAny
{
    /**
     * The gate instance.
     *
     * @var Gate
     */
    protected $gate;

    /**
     * Create a new middleware instance.
     *
     *
     * @return void
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * Handle an incoming request.
     *
     * @param  string  $abilities
     * @param  array|null  $models
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next, $abilities, ...$models)
    {
        if (str($abilities)->contains('|')) {
            $abilities = str($abilities)->split('/\|/')->filter()->all();
        }

        if (! $this->gate->any($abilities, $this->getGateArguments($request, $models))) {
            throw new AuthorizationException;
        }

        return $next($request);
    }

    /**
     * Get the arguments parameter for the gate.
     *
     * @param  Request  $request
     * @param  array|null  $models
     * @return Collection<array-key, \Illuminate\Database\Eloquent\Model>|array
     */
    protected function getGateArguments($request, $models)
    {
        if (is_null($models)) {
            return [];
        }

        return collect($models)->map(fn ($model) => $model instanceof Model
            ? $model
            : $this->getModel($request, $model));
    }

    /**
     *  Get the model to authorize.
     *
     * @param  Request  $request
     * @param  string  $model
     * @return null|object|string
     */
    protected function getModel($request, $model)
    {
        if ($this->isClassName($model)) {
            return trim($model);
        }

        return $request->route($model, null) ?: (
            (preg_match("/^['\"](.*)['\"]$/", trim($model), $matches))
            ? $matches[1]
            : null
        );
    }

    /**
     * Checks if the given string looks like a fully qualified class name.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isClassName($value)
    {
        return strpos($value, '\\') !== false;
    }
}
