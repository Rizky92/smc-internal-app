<?php

namespace Tests\Feature\Livewire\RekamMedis;

use App\Livewire\Pages\RekamMedis\LaporanDemografi;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports. dataPerSheet() has real age-bucketing logic (an intentional
 * switch(true) fallthrough building eight age-group flags) but it only runs
 * during export and never on-screen - exercised here by actually running
 * the export rather than fixturing all eight age brackets.
 */
class LaporanDemografiTest extends TestCase
{
    private const PERMISSION = 'rekam-medis.laporan-demografi.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanDemografi::class);
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

    /**
     * @test
     */
    public function exports_without_crashing(): void
    {
        $this->report()
            ->call('beginExcelExport')
            ->assertFileDownloaded();
    }
}
