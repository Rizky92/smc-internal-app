<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\User\ManajemenUser;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the Livewire component contract.
 *
 * Exercised the way a browser drives it — set a public property, call a public
 * method, read what the component renders. Nothing here inspects the query it
 * builds, so the test survives a rewrite of getUsersProperty() and fails only if
 * the screen stops behaving.
 */
class ManajemenUserTest extends TestCase
{
    /**
     * @test
     *
     * The view opens with wire:init="loadProperties", so the table is empty until
     * the browser asks for it. Nobody should pay for this query on page load.
     */
    public function holds_back_the_table_until_asked_to_load(): void
    {
        $petugas = $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');

        Livewire::actingAs($petugas)
            ->test(ManajemenUser::class)
            ->assertSet('isDeferred', true)
            ->assertDontSee('Budi Santoso')
            ->call('loadProperties')
            ->assertSet('isDeferred', false)
            ->assertSee('Budi Santoso');
    }

    /**
     * @test
     *
     * User::searchColumns() contains DB::raw() expressions, so this path runs
     * Searchable's Expression branch — the one Laravel 10 changed the signature
     * of. Typing in the search box is the only thing that reaches it.
     */
    public function narrows_the_table_to_the_search_term(): void
    {
        $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');
        $this->petugasWithRole('perawat', '99999902', 'Siti Aminah');

        Livewire::actingAs($this->petugasWithRole('perawat', '99999903', 'Admin Uji'))
            ->test(ManajemenUser::class)
            ->call('loadProperties')
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Aminah')
            ->set('cari', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');
    }

    /**
     * @test
     */
    public function filters_down_to_petugas_holding_access_rights(): void
    {
        $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');
        $this->petugasWithPermissions([], '99999902', 'Siti Aminah');

        Livewire::actingAs($this->petugasWithRole('perawat', '99999903', 'Admin Uji'))
            ->test(ManajemenUser::class)
            ->call('loadProperties')
            ->assertSee('Siti Aminah')
            ->set('tampilkanYangMemilikiHakAkses', true)
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');
    }

    /**
     * @test
     *
     * Selecting a petugas has to reach five sibling components, each of which
     * populates its own modal. This is the densest piece of Livewire v3 API in the
     * component — an #[On] listener plus targeted dispatch()->to() calls, both of
     * which replaced v2's $listeners array and emitTo(). If the migration got the
     * event names or the component targets wrong, every one of those modals opens
     * blank, and nothing in the PHP would throw to tell you.
     *
     * manajemen-user.blade.php dispatches {nrp, nama, roles, permissions} as one
     * object, which Livewire 3 spreads as named PHP arguments - passed as four
     * separate positional arguments instead, only the first ever arrived (a fixed
     * 3-argument JS function silently drops anything past it). Named here too, to
     * match the corrected dispatch.
     */
    public function selecting_a_petugas_reaches_all_five_modals(): void
    {
        $petugas = $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');

        Livewire::actingAs($petugas)
            ->test(ManajemenUser::class)
            ->dispatch('user.prepare', nrp: '99999901', nama: 'Budi Santoso', roles: [], permissions: [])
            ->assertDispatchedTo('pages.user.khanza.set-hak-akses', 'khanza.prepare-set')
            ->assertDispatchedTo('pages.user.khanza.transfer-hak-akses', 'khanza.prepare-transfer')
            ->assertDispatchedTo('pages.user.siap.lihat-aktivitas', 'siap.prepare-la')
            ->assertDispatchedTo('pages.user.siap.set-perizinan', 'siap.prepare-set')
            ->assertDispatchedTo('pages.user.siap.transfer-perizinan', 'siap.prepare-transfer');
    }

    /**
     * @test
     */
    public function refuses_impersonation_to_a_petugas_who_is_not_superadmin(): void
    {
        $petugas = $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');

        Livewire::actingAs($petugas)
            ->test(ManajemenUser::class)
            ->call('impersonateAsUser', '99999901')
            ->assertNoRedirect()
            // Not just "nothing happened" — the petugas has to be told why.
            // <x-flash /> renders whatever flashError() put in the session.
            ->assertSee('Anda tidak memiliki izin untuk melakukan tindakan ini!');
    }
}
