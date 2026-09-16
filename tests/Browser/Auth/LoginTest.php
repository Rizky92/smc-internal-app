<?php

namespace Tests\Browser\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * The one real login test: a real browser filling in the real form, against
 * the real Khanza AES_DECRYPT credential check in
 * App\Http\Controllers\Auth\LoginController::store(). Every other Dusk test
 * in this suite uses loginAs() to skip straight past this - it is tested for
 * real exactly once, here.
 */
class LoginTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    /**
     * LoginController::ensureIsNotRateLimited() throttles by
     * Str::transliterate($request->ip()) - a Dusk browser hitting
     * smc-internal-app.test resolves server-side to 127.0.0.1. Clearing this
     * in both setUp() and tearDown() keeps a run of this file from tripping
     * its own limiter, and keeps one run from poisoning the next.
     */
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

    /**
     * @test
     */
    public function logs_in_with_valid_credentials_and_reaches_the_dashboard(): void
    {
        $this->petugasWithPermissions([], '99999902');
        $this->givePlaintextPassword('99999902', 'uji-password-123');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('user', '99999902')
                ->type('pass', 'uji-password-123')
                ->press('Masuk')
                ->assertPathIs('/admin')
                ->assertSee('Selamat Datang');
        });
    }

    /**
     * @test
     *
     * Only one failed attempt - never enough to trip the 3-attempt rate
     * limit, so this always sees the real validation message rather than a
     * throttle message.
     */
    public function refuses_wrong_credentials_with_the_real_error_message(): void
    {
        $this->petugasWithPermissions([], '99999903');
        $this->givePlaintextPassword('99999903', 'uji-password-123');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('user', '99999903')
                ->type('pass', 'password-yang-salah')
                ->press('Masuk')
                ->assertPathIs('/login')
                ->assertSee('Username atau Password salah');
        });
    }
}
