<?php

namespace Tests\Browser\HakAkses;

use App\Models\Aplikasi\Role;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * siap.blade.php's "Edit" button dispatches siap.prepare with a bare
 * positional value: wire:click="$dispatch('siap.prepare', {{ $role->id }})".
 * Livewire's JS $dispatch(name, params) always sends params as the request
 * payload, and the server unpacks it with `...$params` to bind
 * ModalPerizinan::prepare(int $id = -1)'s named parameter - which requires an
 * associative array/object, not a bare scalar. A real click sends the bare
 * int and blows up with "TypeError: Only arrays and Traversables can be
 * unpacked".
 *
 * Livewire::test()->dispatch('siap.prepare', $id) can never catch this: it
 * calls the component method with correct PHP argument passing directly,
 * bypassing the blade attribute (and the browser JS that parses it) entirely
 * - see ModalPerizinanTest, which passes here despite the real button being
 * broken. Only a real click through a real browser exercises the actual
 * wire:click expression.
 */
class SiapEditRoleTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999908';

    private const PASSWORD = 'uji-password-123';

    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('roles')->where('name', 'like', 'UJI-DUSK-%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function clicking_edit_on_a_role_opens_the_modal_instead_of_crashing(): void
    {
        $this->petugasWithRole(config('permission.superadmin_name'), self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        Role::findOrCreate('UJI-DUSK-Role', 'web');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('user', self::NIK)
                ->type('pass', self::PASSWORD)
                ->press('Masuk')
                ->waitForLocation('/admin')
                ->visit('/admin/hak-akses/smc-internal-app')
                ->waitForText('UJI-DUSK-Role')
                ->click('button[wire\\:click*="siap.prepare"]')
                // The input is part of the modal markup from the first paint,
                // so waiting for the element returns before the prepare round
                // trip has filled it in. Wait for the value itself.
                ->waitUntil("document.querySelector('#role-sekarang').value === 'UJI-DUSK-Role'")
                ->assertInputValue('#role-sekarang', 'UJI-DUSK-Role')
                ->assertDontSee('TypeError')
                ->type('#role-sekarang', 'UJI-DUSK-Role-Diubah')
                ->press('Simpan')
                ->waitForText('berhasil diupdate');
        });

        $this->assertDatabaseHas('roles', ['name' => 'UJI-DUSK-Role-Diubah'], 'mysql_smc');
    }
}
