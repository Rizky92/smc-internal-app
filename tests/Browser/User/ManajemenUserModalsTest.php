<?php

namespace Tests\Browser\User;

use App\Models\Aplikasi\Permission;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * manajemen-user.blade.php's row click dispatches 'user.prepare', which
 * ManajemenUser::prepareUser() fans out to five child components via
 * dispatch(...)->to(...). Each child modal (SetHakAkses, TransferHakAkses,
 * TransferPerizinan) listens for its own uniquely-named 'shown.bs.modal'
 * event (khanza.show-sha, khanza.show-tha, siap.show-tp respectively) to
 * flip DeferredLoading's $isDeferred to false and populate its table - but
 * none of the three had a #[On(...)] listener actually registered for that
 * event name, so opening any of them through the real UI always rendered an
 * empty list, regardless of which user's row was clicked.
 *
 * Livewire::test()->dispatch(...) calls the listening method directly by
 * name and can never catch a missing #[On(...)] registration this way - the
 * only way to reach it is a real click through the real modal chain.
 */
class ManajemenUserModalsTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const ACTOR_NIK = '99999900';

    private const SOURCE_NIK = '99999907';

    private const TARGET_NIK = '99999909';

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

    private function logInAsActor(Browser $browser): void
    {
        // Dusk keeps one browser (and its cookies) alive across every test
        // method in this class. A leftover session from a prior test's login
        // makes /login redirect straight to /admin before this fixture's own
        // credentials are ever typed - so start from a clean slate every time.
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->waitFor('input[name=user]')
            ->type('user', self::ACTOR_NIK)
            ->type('pass', self::PASSWORD)
            ->press('Masuk')
            ->waitForLocation('/admin');
    }

    private function openPilihanFor(Browser $browser, string $nik): void
    {
        $browser->visit('/admin/manajemen-user')
            ->waitFor("button[data-nrp=\"{$nik}\"]")
            ->click("button[data-nrp=\"{$nik}\"]")
            ->waitUntil('!document.querySelector("#pilihan").disabled')
            ->click('#pilihan');
    }

    /**
     * @test
     */
    public function set_hak_akses_modal_loads_the_real_khanza_permission_list(): void
    {
        $this->petugasWithRole(config('permission.superadmin_name'), self::ACTOR_NIK);
        $this->givePlaintextPassword(self::ACTOR_NIK, self::PASSWORD);
        $this->petugasWithPermissions([], self::SOURCE_NIK);

        $this->browse(function (Browser $browser) {
            $this->logInAsActor($browser);
            $this->openPilihanFor($browser, self::SOURCE_NIK);

            $browser->click('#button-set-hak-akses')
                ->waitFor('#modal-set-hak-akses')
                // Scoped to the modal itself: the outer ManajemenUser table
                // has its own "Tidak ada..." empty-state and its own text,
                // so an unscoped assertSee/waitForText on the page can pass
                // for reasons that have nothing to do with this modal's own
                // (possibly still-broken) data loading.
                ->waitFor('#modal-set-hak-akses input[id^="sha-"]');
        });
    }

    /**
     * @test
     */
    public function transfer_hak_akses_modal_loads_the_real_user_list(): void
    {
        $this->petugasWithRole(config('permission.superadmin_name'), self::ACTOR_NIK);
        $this->givePlaintextPassword(self::ACTOR_NIK, self::PASSWORD);
        $this->petugasWithPermissions([], self::SOURCE_NIK);
        $this->petugasWithPermissions([], self::TARGET_NIK);

        $this->browse(function (Browser $browser) {
            $this->logInAsActor($browser);
            $this->openPilihanFor($browser, self::SOURCE_NIK);

            $browser->click('#button-transfer-hak-akses')
                ->waitFor('#modal-transfer-hak-akses')
                ->waitFor('#modal-transfer-hak-akses input#tha-'.self::TARGET_NIK);
        });
    }

    /**
     * @test
     */
    public function transfer_perizinan_modal_loads_the_real_user_list(): void
    {
        $this->petugasWithRole(config('permission.superadmin_name'), self::ACTOR_NIK);
        $this->givePlaintextPassword(self::ACTOR_NIK, self::PASSWORD);
        $this->petugasWithPermissions([Permission::findOrCreate('farmasi.stok-darurat.read', 'web')->name], self::SOURCE_NIK);
        $this->petugasWithPermissions([], self::TARGET_NIK);

        $this->browse(function (Browser $browser) {
            $this->logInAsActor($browser);
            $this->openPilihanFor($browser, self::SOURCE_NIK);

            $browser->click('#button-transfer-perizinan')
                ->waitFor('#modal-transfer-perizinan')
                ->waitFor('#modal-transfer-perizinan input#tp-'.self::TARGET_NIK);
        });
    }
}
