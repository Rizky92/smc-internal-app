<?php

namespace Tests\Feature\Livewire\Perawatan;

use App\Livewire\Pages\Perawatan\LaporanHasilMCU;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the on-screen MCU patient list only - smoke-tested per this pass's
 * RO convention. The Excel export (dataPerSheet()) is a much larger unit: it
 * pulls one row per unique lab test name actually found in the period and
 * builds ~100 hardcoded physical-exam columns per patient from a related
 * "penilaianHasilMcu" record - none of which the on-screen Blade view
 * touches (confirmed: it only reads $this->dataPasienPoliMCU). Fixturing
 * that export is out of scope for this pass.
 */
class LaporanHasilMCUTest extends TestCase
{
    private const PERMISSION = 'perawatan.laporan-hasil-pemeriksaan.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanHasilMCU::class);
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
