<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\RKATPenetapan;
use App\Settings\RKATSettings;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the RKAT budget-setting list and its eligibility gate.
 *
 * bisaTetapkanRKAT() is what the paired RKATInputPenetapan modal's "Tambah"
 * button and clickable rows are shown or hidden behind: eligible only inside
 * the Periode Penetapan and holding the permission, for every role, matching
 * the guard on the modal's own writes. The write path itself is already
 * covered by RKATInputPenetapanTest.
 */
class RKATPenetapanTest extends TestCase
{
    private function insidePeriod()
    {
        return app(RKATSettings::class)->tgl_penetapan_awal->copy()->addDay();
    }

    private function outsidePeriod()
    {
        return app(RKATSettings::class)->tgl_penetapan_akhir->copy()->addYear();
    }

    /**
     * @test
     */
    public function is_eligible_inside_the_period_with_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');

        $this->travelTo($this->insidePeriod());

        $bisa = Livewire::actingAs($petugas)
            ->test(RKATPenetapan::class)
            ->instance()
            ->bisaTetapkanRKAT();

        $this->assertTrue($bisa);
    }

    /**
     * @test
     */
    public function is_not_eligible_inside_the_period_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->travelTo($this->insidePeriod());

        $bisa = Livewire::actingAs($petugas)
            ->test(RKATPenetapan::class)
            ->instance()
            ->bisaTetapkanRKAT();

        $this->assertFalse($bisa);
    }

    /**
     * @test
     */
    public function is_not_eligible_outside_the_period_even_with_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');

        $this->travelTo($this->outsidePeriod());

        $bisa = Livewire::actingAs($petugas)
            ->test(RKATPenetapan::class)
            ->instance()
            ->bisaTetapkanRKAT();

        $this->assertFalse($bisa);
    }

    /**
     * @test
     *
     * The Periode Penetapan has no exceptions. Superadmin used to be offered
     * the add button and clickable rows outside it, while every save behind
     * them was refused; working outside the period means opening it first.
     */
    public function superadmin_is_not_eligible_outside_the_period(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        $this->travelTo($this->outsidePeriod());

        $bisa = Livewire::actingAs($petugas)
            ->test(RKATPenetapan::class)
            ->instance()
            ->bisaTetapkanRKAT();

        $this->assertFalse($bisa);
    }
}
