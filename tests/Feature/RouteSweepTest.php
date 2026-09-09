<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

/**
 * Seam A, swept across every permission-gated page in the application.
 *
 * The cases come from the router rather than a hand-written list, so adding a
 * route adds a test and renaming a permission changes one. What it asserts is
 * deliberately shallow — the page renders for someone allowed to see it — but
 * that is the single question a framework upgrade most needs answered, across
 * every screen at once: did anything stop serving?
 *
 * It does not check what the page contains. That belongs at Seam B, against the
 * individual component.
 */
class RouteSweepTest extends TestCase
{
    /**
     * Every authenticated GET route with a fixed URI and a can:/canany: gate.
     *
     * Parameterised routes are excluded: they need real Khanza identifiers to
     * resolve, which this suite has no fixtures for.
     *
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function permissionGatedRoutes(): array
    {
        // Data providers run before the test case boots an application, so this
        // builds a throwaway one purely to read the route table.
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $cases = [];

        foreach ($app['router']->getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $middleware = array_filter($route->gatherMiddleware(), 'is_string');

            if (! in_array('auth', $middleware, true) || str_contains($route->uri(), '{')) {
                continue;
            }

            foreach ($middleware as $m) {
                if (! str_starts_with($m, 'can:') && ! str_starts_with($m, 'canany:')) {
                    continue;
                }

                $arguments = explode(':', $m, 2)[1];

                // The two gates parse their arguments differently. Laravel's own
                // can: takes one ability, with anything after a comma being model
                // arguments. This app's canany: (App\Http\Middleware\AuthorizeAny)
                // takes a pipe-separated list, and is satisfied by any one of
                // them — granting all is the simplest way through.
                $permissions = str_starts_with($m, 'canany:')
                    ? array_filter(explode('|', $arguments))
                    : [explode(',', $arguments)[0]];

                $cases[$route->uri()] = ['/'.$route->uri(), array_values($permissions)];
            }
        }

        return $cases;
    }

    /**
     * Every GET route reachable without logging in, on a fixed URI.
     *
     * These are the waiting-room and information screens. They share
     * layouts/app.blade.php, whose @yield-per-page arrangement is exactly the
     * shape that trips Livewire 3's single-root-tag requirement, so they are
     * worth sweeping even though nobody has to authenticate to reach them.
     *
     * @return array<string, array{0: string}>
     */
    public static function publicRoutes(): array
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $cases = [];

        foreach ($app['router']->getRoutes() as $route) {
            $middleware = array_filter($route->gatherMiddleware(), 'is_string');

            if (! in_array('GET', $route->methods(), true)
                || in_array('auth', $middleware, true)
                || str_contains($route->uri(), '{')
                || str_starts_with($route->uri(), 'api')
                // Routes registered by dev tooling, not by this application.
                || str_starts_with($route->uri(), '_debugbar')
                || str_starts_with($route->uri(), '_ignition')
                || in_array($route->uri(), ['login', 'logout'], true)
                || in_array($route->uri(), self::brokenBeforeThisSweep(), true)) {
                continue;
            }

            $cases[$route->uri()] = ['/'.ltrim($route->uri(), '/')];
        }

        return $cases;
    }

    /**
     * Routes known to be broken, excluded so they do not mask regressions
     * elsewhere. Empty, and worth keeping that way.
     *
     * Both original entries have been dealt with rather than tolerated:
     * print-layout was fixed and rejoined the sweep, covered in more detail by
     * PrintLayoutTest; jadwal-dokter's public duplicate was removed, and
     * JadwalDokterRoutingTest guards against it coming back.
     *
     * Add a URI here only to quarantine a defect you are not fixing yet, with a
     * note saying why, and take it out again once the defect is gone.
     *
     * @return list<string>
     */
    private static function brokenBeforeThisSweep(): array
    {
        return [];
    }

    /**
     * @test
     *
     * @dataProvider publicRoutes
     */
    public function public_page_renders(string $uri): void
    {
        $this->withoutExceptionHandling();

        $this->get($uri)->assertOk();
    }

    /**
     * @test
     *
     * @dataProvider permissionGatedRoutes
     *
     * @param  list<string>  $permissions
     */
    public function page_renders_for_a_petugas_who_is_allowed_to_see_it(string $uri, array $permissions): void
    {
        $petugas = $this->petugasWithPermissions($permissions);

        // Surface the real exception. Across 67 routes, "500 is not 200" is not
        // an answer anyone can act on.
        $this->withoutExceptionHandling();

        $this->actingAs($petugas)->get($uri)->assertOk();
    }
}
