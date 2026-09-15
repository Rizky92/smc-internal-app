<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanTambahanBiayaPasien;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, search doesn't crash).
 */
class LaporanTambahanBiayaPasienTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-tambahan-biaya.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanTambahanBiayaPasien::class);
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
