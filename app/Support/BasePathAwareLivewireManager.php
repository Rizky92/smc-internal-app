<?php

namespace App\Support;

use App\Providers\AppServiceProvider;
use Livewire\LivewireManager;

/**
 * Puts the request's base path back onto Livewire's update endpoint.
 *
 * This application is served from a subdirectory in production: Apache maps
 * /smc onto public/, so requests arrive with a base path of "/smc". Laravel
 * strips it before routing and url() puts it back, which is why everything the
 * application generates itself is correct.
 *
 * Livewire's update URI is not generated that way. HandleRequests::getUpdateUri()
 * asks for a relative route URL, and Illuminate's RouteUrlGenerator explicitly
 * removes $request->getBaseUrl() when building one — so what reaches the browser
 * as data-update-uri is "/livewire/update", anchored at the domain root. Every
 * component round-trip then posts to a URL that does not exist, and because the
 * page itself renders perfectly the failure is silent.
 *
 * Livewire 2 had the app_url config key for exactly this. Livewire 3 removed it
 * and offers setUpdateRoute() instead, which does not help here: that addresses
 * prefixes belonging to Laravel's own routing (locale segments, tenant slugs),
 * whereas a base path is stripped before routing ever sees it. Registering the
 * route as /smc/livewire/update would have Laravel match it against a path info
 * of /livewire/update and fail in a new way.
 *
 * @see AppServiceProvider::registerBasePathAwareLivewire()
 */
class BasePathAwareLivewireManager extends LivewireManager
{
    public function getUpdateUri(): string
    {
        $uri = parent::getUpdateUri();

        $basePath = request()->getBaseUrl();

        // Empty whenever the application owns the domain root — a developer on
        // a vhost, and the test suite. Prepending is then a no-op, so this
        // needs no environment check.
        if ($basePath === '') {
            return $uri;
        }

        // Should Livewire ever start including the base path itself, prepending
        // it again would break every round-trip just as thoroughly as omitting
        // it does now. Staying idempotent means that upstream fix would make
        // this class redundant rather than harmful.
        if (str_starts_with($uri, $basePath.'/')) {
            return $uri;
        }

        return $basePath.$uri;
    }
}
