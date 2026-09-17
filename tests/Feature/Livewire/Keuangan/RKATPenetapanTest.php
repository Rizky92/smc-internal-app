<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\RKATPenetapan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the RKAT budget-setting list and its eligibility gate.
 *
 * bisaTetapkanRKAT() is what the paired RKATInputPenetapan modal's "Tambah"
 * button and clickable rows are shown or hidden behind: eligible inside the
 * Periode Penetapan when holding the permission, or at any time as superadmin.
 * The modal's writes hold superadmin to the period all the same. The write path itself is already
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

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->statement('set foreign_key_checks = 0');
        $smc->table('anggaran_bidang')->delete();
        $smc->table('anggaran')->where('nama', 'Kategori Uji')->delete();
        $smc->table('bidang')->where('nama', 'Bidang Uji')->delete();
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    /**
     * @test
     *
     * Years with a Penetapan RKAT plus the Tahun RKAT (2026 in the test schema),
     * newest first — not a continuous range from the first Penetapan, which
     * listed years in between that hold nothing.
     */
    public function offers_the_years_with_penetapan_and_the_tahun_rkat(): void
    {
        AnggaranBidang::create([
            'anggaran_id'      => Anggaran::create(['nama' => 'Kategori Uji'])->id,
            'bidang_id'        => Bidang::create(['nama' => 'Bidang Uji'])->id,
            'tahun'            => 2024,
            'nominal_anggaran' => 1000,
        ]);

        $tahun = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(RKATPenetapan::class)
            ->get('dataTahun');

        $this->assertSame([2026, 2024], array_keys($tahun));
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
     * Superadmin keeps access to the Penetapan actions outside the Periode
     * Penetapan. Saving them outside it is still refused by the modal, which
     * RKATInputPenetapanTest covers.
     */
    public function superadmin_is_eligible_outside_the_period(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        $this->travelTo($this->outsidePeriod());

        $bisa = Livewire::actingAs($petugas)
            ->test(RKATPenetapan::class)
            ->instance()
            ->bisaTetapkanRKAT();

        $this->assertTrue($bisa);
    }
}
