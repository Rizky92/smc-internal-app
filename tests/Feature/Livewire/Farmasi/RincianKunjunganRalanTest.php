<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\RincianKunjunganRalan;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports. The on-screen query is a plain paginated list (mounts, both
 * total-harga branches, search); the Excel export additionally groups rows
 * by no_resep and appends a per-group total row in PHP (see dataPerSheet()),
 * which the on-screen property never does - covered separately by actually
 * running the export.
 */
class RincianKunjunganRalanTest extends TestCase
{
    private const PERMISSION = 'farmasi.rincian-kunjungan-ralan.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(RincianKunjunganRalan::class);
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
    public function both_total_harga_filters_render_without_error(): void
    {
        $test = $this->report()->call('loadProperties');

        $test->set('totalHarga', 'below_100k')->assertOk();
        $test->set('totalHarga', 'above_100k')->assertOk();
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

    /**
     * @test
     *
     * dataPerSheet() groups the same rows by no_resep and pushes a synthetic
     * "Total" row into each group - exercised here since the on-screen query
     * never runs that code at all.
     */
    public function exports_without_crashing(): void
    {
        $this->report()
            ->call('beginExcelExport')
            ->assertFileDownloaded();
    }
}
