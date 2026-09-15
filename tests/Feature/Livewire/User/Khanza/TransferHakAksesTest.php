<?php

namespace Tests\Feature\Livewire\User\Khanza;

use App\Livewire\Pages\User\Khanza\TransferHakAkses;
use App\Models\Aplikasi\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: copying one user's SIMRS Khanza permission flags onto others.
 *
 * Every column on `user` past id_user/password is a Khanza permission flag
 * stored as the literal string "true"/"false" (see User::scopeWithHakAkses()'s
 * blanket BooleanCast over every column). save()'s "soft transfer" filters
 * $hakAkses down to only the source's "true" columns before the mass
 * update - a target's own value for a column the source has set to "false"
 * is never touched, not forced false. That distinction is invisible unless a
 * target starts with a value the transfer would otherwise flip.
 */
class TransferHakAksesTest extends TestCase
{
    /**
     * @return array<string, bool>
     */
    private function checked(string ...$niks): array
    {
        return collect($niks)->mapWithKeys(fn (string $nik): array => [$nik => true])->all();
    }

    private function setHakAkses(string $nik, string $penyakit, string $obatPenyakit): void
    {
        DB::connection('mysql_sik')
            ->table('user')
            ->whereRaw('AES_DECRYPT(id_user, ?) = ?', ['nur', $nik])
            ->update(['penyakit' => $penyakit, 'obat_penyakit' => $obatPenyakit]);
    }

    private function hakAkses(string $nik): object
    {
        return DB::connection('mysql_sik')
            ->table('user')
            ->whereRaw('AES_DECRYPT(id_user, ?) = ?', ['nur', $nik])
            ->select('penyakit', 'obat_penyakit')
            ->first();
    }

    /**
     * @test
     */
    public function refuses_to_transfer_without_the_superadmin_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $this->petugasWithPermissions([], '99999902');

        $this->setHakAkses('99999901', 'true', 'true');
        $this->setHakAkses('99999902', 'false', 'false');

        Livewire::actingAs($petugas)
            ->test(TransferHakAkses::class)
            ->set('nrp', '99999901')
            ->set('checkedUsers', $this->checked('99999902'))
            ->call('save')
            ->assertDispatched('data-denied');

        $target = $this->hakAkses('99999902');
        $this->assertSame('false', $target->penyakit);
        $this->assertSame('false', $target->obat_penyakit);
    }

    /**
     * @test
     *
     * A full transfer overwrites both flags on the target to match the
     * source exactly, including flipping a flag the target already had set.
     */
    public function fully_transfers_every_flag_to_the_checked_users(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $this->petugasWithPermissions([], '99999902');

        $this->setHakAkses('99999901', 'true', 'false');
        $this->setHakAkses('99999902', 'false', 'true');

        Livewire::actingAs($petugas)
            ->test(TransferHakAkses::class)
            ->set('nrp', '99999901')
            ->set('checkedUsers', $this->checked('99999902'))
            ->set('softTransfer', false)
            ->call('save')
            ->assertDispatched('data-saved');

        $target = $this->hakAkses('99999902');
        $this->assertSame('true', $target->penyakit);
        $this->assertSame('false', $target->obat_penyakit);
    }

    /**
     * @test
     *
     * Soft transfer only carries over the source's "true" flags. The
     * source's obat_penyakit is "false", so it's excluded from the update
     * entirely - the target's own "true" value for it must survive untouched.
     */
    public function soft_transfer_only_carries_over_flags_the_source_has_enabled(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $this->petugasWithPermissions([], '99999902');

        $this->setHakAkses('99999901', 'true', 'false');
        $this->setHakAkses('99999902', 'false', 'true');

        Livewire::actingAs($petugas)
            ->test(TransferHakAkses::class)
            ->set('nrp', '99999901')
            ->set('checkedUsers', $this->checked('99999902'))
            ->set('softTransfer', true)
            ->call('save')
            ->assertDispatched('data-saved');

        $target = $this->hakAkses('99999902');
        $this->assertSame('true', $target->penyakit);
        $this->assertSame('true', $target->obat_penyakit);
    }

    /**
     * @test
     */
    public function preparing_a_transfer_fills_the_source_user(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901', 'Petugas Uji');

        Livewire::actingAs($petugas)
            ->test(TransferHakAkses::class)
            ->dispatch('khanza.prepare-transfer', nrp: '99999901', nama: 'Petugas Uji')
            ->assertSet('nrp', '99999901')
            ->assertSet('nama', 'Petugas Uji');
    }

    /**
     * @test
     *
     * The source user can't transfer permissions onto themselves, so they're
     * excluded from their own candidate list.
     */
    public function excludes_the_source_user_from_the_available_list(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901', 'Sumber Uji');
        $this->petugasWithPermissions([], '99999902', 'Tujuan Uji');

        Livewire::actingAs($petugas)
            ->test(TransferHakAkses::class)
            ->set('nrp', '99999901')
            ->call('loadProperties')
            ->assertSee('Tujuan Uji')
            ->assertDontSee('Sumber Uji');
    }
}
