<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Livewire's two browser-facing URLs, for an application that does not own the
 * domain root.
 *
 * Production serves this application from a subdirectory: Apache maps /smc onto
 * public/, so every request arrives carrying a base path of "/smc" which Laravel
 * strips before routing. url() and asset() both put it back again, which is why
 * layouts/app.blade.php has never had trouble finding its CSS.
 *
 * Livewire 3 does not put it back. It emits two URLs of its own — the src of its
 * script tag, and the data-update-uri that every component round-trip posts to —
 * and both are anchored at the domain root. Livewire 2 supported this case
 * through its asset_url and app_url config keys; Livewire 3 removed both, so
 * upgrading silently took the support away. The browser asks the host root for
 * /livewire/livewire.js, receives a 404, and nothing on the page works.
 *
 * The second URL is the reason this test asserts on both. Fixing only the script
 * src produces a page that loads and then does nothing: every filter, sort and
 * paginate posts to a URL that isn't there. That failure is silent, which is
 * what makes it worth a test rather than a note.
 *
 * Note this is a *base path*, not a route prefix. Livewire's documented remedy,
 * setScriptRoute()/setUpdateRoute(), addresses prefixes that belong to Laravel's
 * own routing — /en for localisation, a tenant slug. Registering those routes
 * under /smc would have Laravel match /smc/livewire/update against a path info
 * of /livewire/update, and fail in a new way. See docs/adr/0001.
 *
 * What is asserted is deliberately shallow: that each URL carries the base path.
 * The rest of the script URL depends on whether Livewire's assets have been
 * published and on app.debug, and neither is the subject here.
 */
class LivewireBasePathTest extends TestCase
{
    /**
     * Livewire only injects its assets into a response where a component
     * actually rendered, so this has to be a real page. Any public Livewire
     * route would do; this one needs no authentication and no fixtures.
     */
    private const URI = '/informasi-kamar';

    private const BASE_PATH = '/smc';

    /**
     * Given in full so the request does not depend on APP_URL, which already
     * carries the base path in this developer's .env and would double it.
     */
    private const HOST = 'http://localhost';

    /**
     * @test
     */
    public function livewire_urls_carry_the_base_path(): void
    {
        $this->withoutExceptionHandling();

        $html = $this->withServerVariables([
            'SCRIPT_NAME'     => self::BASE_PATH.'/index.php',
            'SCRIPT_FILENAME' => self::BASE_PATH.'/index.php',
            'PHP_SELF'        => self::BASE_PATH.'/index.php',
        ])->get(self::HOST.self::BASE_PATH.self::URI)->assertOk()->getContent();

        [$src, $updateUri] = $this->livewireScriptTag($html);

        $this->assertStringStartsWith(
            self::BASE_PATH.'/',
            $this->pathOf($src),
            "Livewire's script src does not carry the base path. The browser will "
            ."resolve it against the host root and 404. Got: {$src}"
        );

        $this->assertStringStartsWith(
            self::BASE_PATH.'/',
            $this->pathOf($updateUri),
            'Livewire\'s data-update-uri does not carry the base path. The page '
            ."will render and then silently fail on every round-trip. Got: {$updateUri}"
        );

        $this->assertStringEndsWith(
            '/livewire/update',
            $updateUri,
            "data-update-uri no longer points at Livewire's update endpoint. Got: {$updateUri}"
        );
    }

    /**
     * The path of a URL that may be absolute or root-relative.
     *
     * Livewire emits the two in different shapes — the update URI is always
     * root-relative, while the script src becomes absolute once the assets are
     * published, because that path builds it with url(). Comparing paths keeps
     * the assertion about the base path rather than about the shape.
     */
    private function pathOf(string $url): string
    {
        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    /**
     * The src and data-update-uri of the script tag Livewire injects.
     *
     * Matching on data-update-uri rather than on the src identifies Livewire's
     * own tag unambiguously — layouts/app.blade.php injects several others.
     *
     * @return array{0: string, 1: string}
     */
    private function livewireScriptTag(string $html): array
    {
        $matched = preg_match(
            '/<script src="([^"]*)"[^>]*data-update-uri="([^"]*)"/',
            $html,
            $matches
        );

        $this->assertSame(
            1,
            $matched,
            'No Livewire script tag in the response. Either the assets were not '
            .'injected, or Livewire changed the markup this test reads.'
        );

        return [$matches[1], $matches[2]];
    }
}
