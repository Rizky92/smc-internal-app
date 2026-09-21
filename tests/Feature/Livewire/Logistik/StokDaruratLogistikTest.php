<?php

namespace Tests\Feature\Livewire\Logistik;

use App\Livewire\Pages\Logistik\StokDaruratLogistik;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, the "hide zero suggested order" toggle doesn't crash,
 * search doesn't crash).
 */
class StokDaruratLogistikTest extends TestCase
{
    private const PERMISSION = 'logistik.stok-darurat.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(StokDaruratLogistik::class);
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
    public function toggle_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('tampilkanSaranOrderNol', false)
            ->assertOk();
    }

    /**
     * @test
     */
    public function search_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('cari', 'kertas')
            ->assertOk();
    }
}
