<?php

namespace Tests\Feature\Livewire\Farmasi;

use App\Livewire\Pages\Farmasi\KunjunganPerBentukObat;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts - both "regular" and "racikan" tabs - search doesn't
 * crash).
 */
class KunjunganPerBentukObatTest extends TestCase
{
    private const PERMISSION = 'farmasi.kunjungan-per-bentuk-obat.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(KunjunganPerBentukObat::class);
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
