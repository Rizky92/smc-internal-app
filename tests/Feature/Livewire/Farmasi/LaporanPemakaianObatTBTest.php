<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\LaporanPemakaianObatTB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, both age-group branches don't crash, search doesn't
 * crash).
 */
class LaporanPemakaianObatTBTest extends TestCase
{
    private const PERMISSION = 'farmasi.laporan-pemakaian-obat-tb.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanPemakaianObatTB::class);
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
    public function both_age_group_filters_render_without_error(): void
    {
        $test = $this->report()->call('loadProperties');

        $test->set('umur', 'anak')->assertOk();
        $test->set('umur', 'dewasa')->assertOk();
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
