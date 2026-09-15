<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanTindakanRadiologi;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, search doesn't crash).
 */
class LaporanTindakanRadiologiTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-tindakan-radiologi.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanTindakanRadiologi::class);
    }

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $this->report()
            ->call('loadProperties')
            ->assertOk();
    }

    /**
     * @test
     */
    public function search_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('cari', 'pasien')
            ->assertOk();
    }
}
