<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\User\Khanza\SetHakAkses;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the modal that grants SIMRS Khanza permissions.
 *
 * This one writes straight into Khanza's own `user` row, whose ~100 enum
 * columns are the permissions. It is reached only by an event dispatched from
 * ManajemenUser, it is restricted to a single role, and it decides what to write
 * by merging defaults from mysql_smc over values held in component state — three
 * different things that a migration can break without raising anything.
 */
class SetHakAksesTest extends TestCase
{
    private function superadminName(): string
    {
        return config('permission.superadmin_name');
    }

    /**
     * @test
     *
     * ManajemenUser announces the selected petugas with khanza.prepare-set. If
     * that name or signature drifts, the modal opens against nobody.
     */
    public function receives_the_selected_petugas_from_manajemen_user(): void
    {
        $petugas = $this->petugasWithRole($this->superadminName(), '99999901');

        Livewire::actingAs($petugas)
            ->test(SetHakAkses::class)
            ->dispatch('khanza.prepare-set', '99999902', 'Budi Santoso')
            ->assertSet('nrp', '99999902')
            ->assertSet('nama', 'Budi Santoso');
    }

    /**
     * @test
     *
     * Khanza permissions are superadmin-only. Anyone else is refused, and the
     * target's row must be untouched.
     */
    public function refuses_a_petugas_who_is_not_superadmin(): void
    {
        $petugas = $this->petugasWithRole('perawat', '99999901');
        $this->petugasWithPermissions([], '99999902');

        Livewire::actingAs($petugas)
            ->test(SetHakAkses::class)
            ->dispatch('khanza.prepare-set', '99999902', 'Budi Santoso')
            ->call('showModal')
            ->set('checkedHakAkses', ['pasien' => true])
            ->dispatch('khanza.set')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved');

        $this->assertNotSame('true', $this->hakAksesValue('99999902', 'pasien'));
    }

    /**
     * @test
     *
     * The permission is a string enum in Khanza, not a boolean, so what lands in
     * the column matters as much as that something did.
     *
     * showModal() is called first because that is the real sequence, and because
     * save() depends on it: getHakAksesKhanzaProperty() returns a plain array
     * while the modal is still deferred, and save() calls ->mapWithKeys() on it.
     * Dispatching khanza.set before the modal opens is a fatal error rather than
     * a refusal.
     */
    public function a_superadmin_writes_the_checked_permissions_into_khanza(): void
    {
        $petugas = $this->petugasWithRole($this->superadminName(), '99999901');
        $this->petugasWithPermissions([], '99999902');

        Livewire::actingAs($petugas)
            ->test(SetHakAkses::class)
            ->dispatch('khanza.prepare-set', '99999902', 'Budi Santoso')
            ->call('showModal')
            ->set('checkedHakAkses', ['pasien' => true])
            ->dispatch('khanza.set')
            ->assertDispatched('data-saved')
            ->assertNotDispatched('data-denied');

        $this->assertSame('true', $this->hakAksesValue('99999902', 'pasien'));
    }

    /**
     * Read one Khanza permission column straight from the row, matching on the
     * decrypted id_user because that is the only thing tying it to a NRP.
     */
    private function hakAksesValue(string $nrp, string $column): ?string
    {
        return DB::connection('mysql_sik')
            ->table('user')
            ->whereRaw('AES_DECRYPT(id_user, ?) = ?', [config('khanza.app.userkey'), $nrp])
            ->value($column);
    }
}
