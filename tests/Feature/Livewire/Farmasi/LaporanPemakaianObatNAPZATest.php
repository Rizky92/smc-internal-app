<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\LaporanPemakaianObatNAPZA;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts - both "narkotika" and "psikotropika" tabs - search
 * doesn't crash).
 */
class LaporanPemakaianObatNAPZATest extends TestCase
{
    private const PERMISSION = 'farmasi.laporan-pemakaian-obat-napza.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanPemakaianObatNAPZA::class);
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
            ->set('cari', 'paracetamol')
            ->assertOk();
    }
}
