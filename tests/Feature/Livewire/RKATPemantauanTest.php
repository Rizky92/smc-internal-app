<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\RKATPemantauan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Budget against spending, per bidang and per year.
 *
 * Unlike the other two ledger reports this one is a tree — roots, their
 * descendants, and each unit's budget lines with spending summed through
 * pemakaian_anggaran. It also carries a year selector, and the figures on screen
 * and the figures in the Excel export are produced by two entirely separate
 * pieces of code, which is the interesting part.
 *
 *   Unit Uji, 2026   budget 1.000.000, spent 250.000  ->  left 750.000, 25%
 *   Unit Uji, 2025   budget   400.000, nothing spent
 *
 * See RKATInputPenetapanTest::tearDown() for why cleanup drops the foreign key
 * checks.
 */
class RKATPemantauanTest extends TestCase
{
    private const PERMISSION = 'keuangan.rkat-pemantauan.read';

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->statement('set foreign_key_checks = 0');
        foreach (['pemakaian_anggaran_detail', 'pemakaian_anggaran', 'anggaran_bidang', 'anggaran', 'bidang'] as $table) {
            $smc->table($table)->delete();
        }
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    /**
     * The report renders roots and their descendants, so a budget has to hang off
     * a child bidang to appear at all.
     *
     * @return array{0: Bidang, 1: Anggaran}
     */
    private function bidangTree(): array
    {
        $induk = Bidang::create(['nama' => 'Bidang Induk Uji']);
        $unit = Bidang::create(['nama' => 'Unit Uji', 'parent_id' => $induk->id]);

        return [$unit, Anggaran::create(['nama' => 'Kategori Uji'])];
    }

    private function anggaranBidang(Bidang $unit, Anggaran $anggaran, string $tahun, float $nominal): AnggaranBidang
    {
        return AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $unit->id,
            'tahun'            => $tahun,
            'nominal_anggaran' => $nominal,
        ]);
    }

    private function belanja(AnggaranBidang $rkat, float $nominal): void
    {
        PemakaianAnggaran::create([
            'judul'              => 'Belanja Uji',
            'tgl_dipakai'        => '2026-04-01',
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ])->detail()->createMany([
            ['keterangan' => 'Barang Uji', 'nominal' => $nominal],
        ]);
    }

    private function report(string $tahun = '2026')
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)
            ->test(RKATPemantauan::class)
            ->set('tahun', $tahun)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * Spending is summed through pemakaian_anggaran into its detail lines, and
     * what is left is the budget less that sum.
     */
    public function reports_budget_spending_and_what_is_left(): void
    {
        [$unit, $anggaran] = $this->bidangTree();
        $this->belanja($this->anggaranBidang($unit, $anggaran, '2026', 1000000), 250000);

        $this->report()
            ->assertSee('Rp. 1.000.000')
            ->assertSee('Rp. 250.000')
            ->assertSee('Rp. 750.000')
            ->assertSee('25,00%');
    }

    /**
     * @test
     *
     * The year selector is bound to $tahun and the export filters on it. The
     * figures on screen have to agree, or the spreadsheet somebody downloads
     * contradicts the page they downloaded it from.
     */
    public function shows_only_the_selected_year(): void
    {
        [$unit, $anggaran] = $this->bidangTree();
        $this->belanja($this->anggaranBidang($unit, $anggaran, '2026', 1000000), 250000);
        $this->anggaranBidang($unit, $anggaran, '2025', 400000);

        $this->report('2026')
            ->assertSee('Rp. 1.000.000')
            ->assertDontSee('Rp. 400.000');
    }

    /**
     * @test
     *
     * A budget of nothing is accepted by RKATInputPenetapan, whose rule is
     * min:0. Spending against it then divides by that nothing. The view already
     * guards the same division; the export is what does not.
     */
    public function exports_a_budget_of_zero_without_dividing_by_it(): void
    {
        [$unit, $anggaran] = $this->bidangTree();
        $this->belanja($this->anggaranBidang($unit, $anggaran, '2026', 0), 50000);

        $this->report()
            ->call('beginExcelExport')
            ->assertFileDownloaded();
    }
}
