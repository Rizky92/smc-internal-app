<?php

namespace Tests\Feature\Livewire\Marketing;

use App\Livewire\Pages\Marketing\SummaryBillingMCU;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only report, smoke-tested per this pass's convention for pure
 * RO reports (mounts, search doesn't crash).
 */
class SummaryBillingMCUTest extends TestCase
{
    private const PERMISSION = 'marketing.summary-billing-mcu.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(SummaryBillingMCU::class);
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
