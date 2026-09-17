<?php

namespace Tests\Browser\HakAkses;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use Laravel\Dusk\Browser;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * Ticking and unticking permissions on a SIAP role, with real clicks.
 *
 * ModalPerizinanTest sets checkedPermissions.{id} to true, which is what the
 * Livewire 3 bundle's Alpine code sends for a checkbox bound to a non-array
 * path. That is a reading of the JavaScript, not an observation of it. Here the
 * browser does the sending: the label is clicked the way a user clicks it (the
 * input itself is visually hidden by Bootstrap's custom-control), the form is
 * submitted, and the database says what arrived.
 *
 * Granting was broken by the Livewire 3 migration while revoking kept working,
 * so both directions are pinned.
 */
class SiapPermissionCheckboxTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999909';

    private const PASSWORD = 'uji-password-123';

    /**
     * A superadmin, a role holding permission A, and permission B unassigned,
     * then the role opened for editing with its current permissions loaded.
     *
     * @return array{0: Role, 1: Permission, 2: Permission}
     */
    private function roleDibuka(Browser $browser): array
    {
        $this->petugasWithRole(config('permission.superadmin_name'), self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        $a = Permission::findOrCreate('uji-dusk.izin-a', 'web');
        $b = Permission::findOrCreate('uji-dusk.izin-b', 'web');

        $role = Role::findOrCreate('UJI-DUSK-Izin', 'web');
        $role->givePermissionTo($a);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Dusk keeps one browser for every test in the class. Without this the
        // second test is still signed in, /login redirects, and the form is not
        // there to fill in.
        $browser->visit('/login');
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->type('user', self::NIK)
            ->type('pass', self::PASSWORD)
            ->press('Masuk')
            ->waitForLocation('/admin')
            ->visit('/admin/hak-akses/smc-internal-app')
            ->waitForText('UJI-DUSK-Izin')
            ->click("button[wire\\:click*=\"{ id: {$role->id} }\"]")
            // Both the name and A's box are filled by the prepare round trip;
            // the markup exists before either arrives.
            ->waitUntil("document.querySelector('#role-sekarang').value === 'UJI-DUSK-Izin'")
            ->waitUntil("document.getElementById('permission-{$a->id}').checked === true")
            ->assertNotChecked("#permission-{$b->id}");

        return [$role, $a, $b];
    }

    /**
     * @return list<string>
     */
    private function izinRole(Role $role): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return Role::findById($role->id)->permissions()->orderBy('name')->pluck('name')->all();
    }

    /**
     * @test
     */
    public function unticking_a_permission_in_the_browser_revokes_it(): void
    {
        $role = null;

        $this->browse(function (Browser $browser) use (&$role) {
            [$role, $a] = $this->roleDibuka($browser);

            $browser->click("label[for=\"permission-{$a->id}\"]")
                ->waitUntil("document.getElementById('permission-{$a->id}').checked === false")
                ->press('Simpan')
                ->waitForText('berhasil diupdate');
        });

        $this->assertSame([], $this->izinRole($role));
    }

    /**
     * See ModalPerizinanTest::ticking_a_permission_grants_it(). Before the fix,
     * the box was ticked, the save reported success, and the role still held
     * only what it had.
     *
     * @test
     */
    public function ticking_a_permission_in_the_browser_grants_it(): void
    {
        $role = null;

        $this->browse(function (Browser $browser) use (&$role) {
            [$role, , $b] = $this->roleDibuka($browser);

            $browser->click("label[for=\"permission-{$b->id}\"]")
                ->waitUntil("document.getElementById('permission-{$b->id}').checked === true")
                ->press('Simpan')
                ->waitForText('berhasil diupdate');
        });

        $this->assertSame(['uji-dusk.izin-a', 'uji-dusk.izin-b'], $this->izinRole($role));
    }
}
