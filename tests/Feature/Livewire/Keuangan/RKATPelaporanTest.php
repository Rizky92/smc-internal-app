<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\RKATPelaporan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the RKAT spending report, filtered by year and bidang.
 *
 * penggunaanRKAT() narrows by year through a whereHas() on the related
 * AnggaranBidang, not a column on PemakaianAnggaran itself - a report
 * seeded across two different budget years must only show the one the
 * "tahun" filter is currently set to. The write path (RKATInputPelaporanTest)
 * already covers create/update.
 */
class RKATPelaporanTest extends TestCase
{
    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->statement('set foreign_key_checks = 0');
        $smc->table('pemakaian_anggaran')->where('judul', 'like', 'Uji%')->delete();
        $smc->table('anggaran_bidang')->delete();
        $smc->table('anggaran')->where('nama', 'like', 'Kategori Uji%')->delete();
        $smc->table('bidang')->where('nama', 'like', 'Bidang Uji%')->delete();
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    private function pemakaian(string $judul, int $tahun): void
    {
        $rkat = AnggaranBidang::create([
            'anggaran_id'      => Anggaran::create(['nama' => 'Kategori Uji '.$tahun])->id,
            'bidang_id'        => Bidang::create(['nama' => 'Bidang Uji '.$tahun])->id,
            'tahun'            => $tahun,
            'nominal_anggaran' => 1000000,
        ]);

        PemakaianAnggaran::create([
            'judul'              => $judul,
            'tgl_dipakai'        => "{$tahun}-01-15",
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ]);
    }

    /**
     * @test
     */
    public function narrows_the_report_to_the_selected_year(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->pemakaian('Uji Pembelian 2025', 2025);
        $this->pemakaian('Uji Pembelian 2026', 2026);

        Livewire::actingAs($petugas)
            ->test(RKATPelaporan::class)
            ->set('tahun', '2025')
            ->assertSee('Uji Pembelian 2025')
            ->assertDontSee('Uji Pembelian 2026');
    }
}
