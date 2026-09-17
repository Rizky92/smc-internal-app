<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\TarifRanap;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the tarif rawat inap list.
 *
 * A pure read-only report - the only write path is the paired
 * ImportTarifRanap modal, tested separately. This suite is a smoke test:
 * the query mounts and runs without error, and the "Import" trigger is
 * gated on the same permission the modal itself checks.
 */
class TarifRanapTest extends TestCase
{
    /**
     * @test
     */
    public function loads_the_list_without_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-ranap.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifRanap::class)
            ->call('loadProperties')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function searching_does_not_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-ranap.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifRanap::class)
            ->call('loadProperties')
            ->set('cari', 'tidak-ada-yang-cocok-dengan-ini')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function shows_the_import_trigger_only_with_permission(): void
    {
        $tanpaIzin = $this->petugasWithPermissions(['keuangan.tarif-ranap.read'], '99999901');
        $denganIzin = $this->petugasWithPermissions(['keuangan.tarif-ranap.read', 'keuangan.tarif-ranap.create'], '99999902');

        Livewire::actingAs($tanpaIzin)
            ->test(TarifRanap::class)
            ->assertDontSee('Import');

        Livewire::actingAs($denganIzin)
            ->test(TarifRanap::class)
            ->assertSee('Import');
    }
}
