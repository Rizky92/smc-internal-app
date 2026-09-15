<?php

namespace Tests\Feature\Livewire\Marketing;

use App\Livewire\Pages\Marketing\LaporanPenggunaanAlkes;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Seam B: twelve separate paginated tabs (six alkes tindakan x ralan/ranap
 * unions, plus six radiology scans), smoke-tested per this pass's RO
 * convention - mounting already touches every one of them, since the Blade
 * view renders every tab. Search is never chained on any of them, so there
 * is nothing to test there.
 *
 * classifyAlkesUnit() is real, pure-PHP logic used only by the Excel
 * export's summary sheet (Ranap / MCU / Poli, based on status and kd_poli)
 * - cheap to verify directly via reflection since it takes a plain object
 * and needs no database fixtures at all.
 */
class LaporanPenggunaanAlkesTest extends TestCase
{
    private const PERMISSION = 'marketing.penggunaan-alkes.read';

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanPenggunaanAlkes::class);
    }

    private function classify(object $item): string
    {
        $component = $this->report()->instance();
        $method = new ReflectionMethod($component, 'classifyAlkesUnit');
        $method->setAccessible(true);

        return $method->invoke($component, $item);
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
    public function classifies_inpatients_as_ranap_regardless_of_poli(): void
    {
        $this->assertSame('Ranap', $this->classify((object) ['status' => 'Ranap', 'kd_poli' => 'U0036']));
    }

    /**
     * @test
     */
    public function classifies_outpatients_from_the_mcu_poli_as_mcu(): void
    {
        $this->assertSame('MCU', $this->classify((object) ['status' => 'Ralan', 'kd_poli' => 'U0036']));
    }

    /**
     * @test
     */
    public function classifies_other_outpatients_as_poli(): void
    {
        $this->assertSame('Poli', $this->classify((object) ['status' => 'Ralan', 'kd_poli' => 'U0001']));
    }
}
