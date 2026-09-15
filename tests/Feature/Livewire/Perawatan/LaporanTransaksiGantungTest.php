<?php

namespace Tests\Feature\Livewire\Perawatan;

use App\Livewire\Pages\Perawatan\LaporanTransaksiGantung;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, both jenis/status branches don't crash, search doesn't
 * crash).
 */
class LaporanTransaksiGantungTest extends TestCase
{
    private const PERMISSION = 'perawatan.laporan-transaksi-gantung.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanTransaksiGantung::class);
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
    public function jenis_and_status_toggles_do_not_crash(): void
    {
        $test = $this->report()->call('loadProperties');

        $test->set('jenis', 'ranap')->assertOk();
        $test->set('status', 'belum')->assertOk();
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
