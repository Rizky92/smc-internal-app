<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\TarifOperasi;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the tarif operasi list.
 *
 * A pure read-only report - the only write path is the paired
 * ImportTarifOperasi modal, tested separately. This suite is a smoke test:
 * the query mounts and runs without error, and the "Import" trigger is
 * gated on the same permission the modal itself checks.
 */
class TarifOperasiTest extends TestCase
{
    /**
     * @test
     */
    public function loads_the_list_without_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-operasi.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifOperasi::class)
            ->call('loadProperties')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function searching_does_not_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-operasi.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifOperasi::class)
            ->call('loadProperties')
            ->set('cari', 'tidak-ada-yang-cocok-dengan-ini')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function shows_the_import_trigger_only_with_permission(): void
    {
        $tanpaIzin = $this->petugasWithPermissions(['keuangan.tarif-operasi.read'], '99999901');
        $denganIzin = $this->petugasWithPermissions(['keuangan.tarif-operasi.read', 'keuangan.tarif-operasi.create'], '99999902');

        Livewire::actingAs($tanpaIzin)
            ->test(TarifOperasi::class)
            ->assertDontSee('Import');

        Livewire::actingAs($denganIzin)
            ->test(TarifOperasi::class)
            ->assertSee('Import');
    }
}
