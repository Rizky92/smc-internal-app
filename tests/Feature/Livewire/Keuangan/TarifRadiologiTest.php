<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\TarifRadiologi;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the tarif radiologi list.
 *
 * A pure read-only report - the only write path is the paired
 * ImportTarifRadiologi modal, tested separately. This suite is a smoke
 * test: the query mounts and runs without error, and the "Import" trigger
 * is gated on the same permission the modal itself checks.
 */
class TarifRadiologiTest extends TestCase
{
    /**
     * @test
     */
    public function loads_the_list_without_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-radiologi.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifRadiologi::class)
            ->call('loadProperties')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function searching_does_not_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.tarif-radiologi.read'], '99999901');

        Livewire::actingAs($petugas)
            ->test(TarifRadiologi::class)
            ->call('loadProperties')
            ->set('cari', 'tidak-ada-yang-cocok-dengan-ini')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function shows_the_import_trigger_only_with_permission(): void
    {
        $tanpaIzin = $this->petugasWithPermissions(['keuangan.tarif-radiologi.read'], '99999901');
        $denganIzin = $this->petugasWithPermissions(['keuangan.tarif-radiologi.read', 'keuangan.tarif-radiologi.create'], '99999902');

        Livewire::actingAs($tanpaIzin)
            ->test(TarifRadiologi::class)
            ->assertDontSee('Import');

        Livewire::actingAs($denganIzin)
            ->test(TarifRadiologi::class)
            ->assertSee('Import');
    }
}
