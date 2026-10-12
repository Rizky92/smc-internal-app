<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\PerbandinganBarangPO;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, the "only show discrepancies" toggle doesn't crash,
 * search doesn't crash).
 */
class PerbandinganBarangPOTest extends TestCase
{
    private const PERMISSION = 'farmasi.perbandingan-po-obat.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(PerbandinganBarangPO::class);
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
    public function barang_selisih_toggle_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('barangSelisih', true)
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
