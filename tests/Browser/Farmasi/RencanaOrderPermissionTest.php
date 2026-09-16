<?php

namespace Tests\Browser\Farmasi;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * Route-level permission enforcement - something none of the 441
 * Livewire::test() Feature tests can ever cover, since Livewire::test()
 * instantiates a component directly and never goes through routing or
 * middleware at all. RencanaOrder (Farmasi/stok-darurat) is gated by
 * ->middleware('can:farmasi.stok-darurat.read') directly on the route.
 *
 * App\Exceptions\Handler::render() deliberately rewrites every
 * AuthorizationException into a NotFoundHttpException before Laravel ever
 * renders it - denied routes come back as a plain 404, not a 403, so an
 * unauthorized user can't tell a gated route apart from one that doesn't
 * exist. Confirmed by running this test and reading the actual failure
 * screenshot before trusting the plan's original "403" assumption.
 *
 * Browser::loginAs() cannot be used here: it builds a URL from the User
 * model's raw primary key (id_user), which is AES-encrypted binary data.
 * One of those bytes reliably produces a literal, unescaped "/" once
 * URL-encoded, splitting Dusk's internal /_dusk/login/{userId} route into the
 * wrong path segments, so the vendor login controller never actually
 * authenticates - the browser silently lands back on /login. Driving the
 * real form (as LoginTest does) sidesteps the encrypted-PK incompatibility
 * entirely.
 */
class RencanaOrderPermissionTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const URL = '/admin/farmasi/stok-darurat';

    private const PASSWORD = 'uji-password-123';

    private function throttleKey(): string
    {
        return Str::transliterate('127.0.0.1');
    }

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear($this->throttleKey());
    }

    protected function tearDown(): void
    {
        RateLimiter::clear($this->throttleKey());

        parent::tearDown();
    }

    private function logIn(Browser $browser, string $nik): void
    {
        $browser->visit('/login')
            ->type('user', $nik)
            ->type('pass', self::PASSWORD)
            ->press('Masuk')
            ->assertPathIs('/admin');
    }

    /**
     * @test
     */
    public function blocks_access_without_the_permission(): void
    {
        $this->petugasWithPermissions([], '99999904');
        $this->givePlaintextPassword('99999904', self::PASSWORD);

        $this->browse(function (Browser $browser) {
            $this->logIn($browser, '99999904');

            $browser->visit(self::URL)
                ->assertSee('404');
        });
    }

    /**
     * @test
     */
    public function allows_access_with_the_permission(): void
    {
        $this->petugasWithPermissions(['farmasi.stok-darurat.read'], '99999905');
        $this->givePlaintextPassword('99999905', self::PASSWORD);

        $this->browse(function (Browser $browser) {
            $this->logIn($browser, '99999905');

            $browser->visit(self::URL)
                ->assertDontSee('404')
                ->assertSee('Stok minimal');
        });
    }
}
