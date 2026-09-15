<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\LaporanPemakaianObatMorphine;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports. Mounting already exercises all four per-drug-code paginated
 * properties (one per hardcoded kode_brng) plus the bangsal/obat option
 * lists; search doesn't crash either.
 */
class LaporanPemakaianObatMorphineTest extends TestCase
{
    private const PERMISSION = 'farmasi.laporan-pemakaian-obat-morphine.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanPemakaianObatMorphine::class);
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
