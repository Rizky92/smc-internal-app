<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\LaporanProduksiTahunan;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: a dashboard of yearly sums, each drawn from its own already-scoped
 * report (kunjungan/pendapatan/pembelian, one static call per source).
 * kunjunganTotal, pendapatanObatTotal and totalBersihPembelianFarmasi add or
 * subtract those sources per month in PHP, but each source spans a different
 * Farmasi domain (ralan/ranap/IGD/walk-in visits, four income streams,
 * purchasing vs. supplier returns) - full arithmetic verification would mean
 * fixturing eight independent domains for one report. Smoke-tested per this
 * pass's RO convention instead: rendering the page already calls every
 * computed property (including the three that sum), so a real SQL error or a
 * broken key lookup (e.g. a month missing from one source) surfaces here.
 */
class LaporanProduksiTahunanTest extends TestCase
{
    private const PERMISSION = 'farmasi.laporan-produksi.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanProduksiTahunan::class);
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
    public function switching_years_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('tahun', '2023')
            ->assertOk();
    }
}
