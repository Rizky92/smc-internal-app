<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanFakturPajakUmum;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the pulled ("tarikan") tax-invoice snapshot for UMUM/PERSONAL (A09)
 * patients, once it has already been materialized.
 *
 * simpanTarikan()/updateHargaObat() (only reachable through the real Excel
 * export, via beginExcelExport()) build this snapshot by joining across a
 * dozen unioned billing/registration sources in mysql_sik - reproducing that
 * with fixtures is out of scope for this pass. What is covered here is the
 * read side once a snapshot already exists: it must be scoped to this page's
 * own "menu" (fp-umum), never leak another report's pulled rows, and search
 * must not crash while browsing a historical pull.
 *
 * Two things worth a decision rather than a silent fix: the export/tarikan
 * write is gated by the exact same route permission as merely viewing the
 * report (`keuangan.laporan-faktur-pajak.read` - see routes/web.php), with no
 * separate write/export permission; and simpanTarikan() inserts its header
 * row, then all its detail rows, then runs updateHargaObat()'s correction
 * UPDATE, with no DB::transaction() wrapping the sequence, so a failure
 * partway through leaves a partial snapshot under that tgl_tarikan.
 */
class LaporanFakturPajakUmumTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-faktur-pajak.read';

    private const MENU = 'fp-umum';

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->table('faktur_pajak_ditarik_detail')->where('no_rawat', 'like', 'UJI%')->delete();
        $smc->table('faktur_pajak_ditarik')->where('no_rawat', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function tarikan(string $menu, string $noRawat, string $tglTarikan): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->table('faktur_pajak_ditarik')->insert([
            'no_rawat' => $noRawat, 'kode_transaksi' => '04', 'tgl_bayar' => '2026-03-05',
            'jam_bayar' => '09:00:00', 'status_lanjut' => 'Ralan', 'id_tku_penjual' => '0000000000000000',
            'no_rkm_medis' => 'RM-'.$noRawat, 'nik_pasien' => '1234567890123456',
            'nama_pasien' => 'Pasien '.$noRawat, 'alamat_pasien' => 'Alamat Uji',
            'kode_asuransi' => 'UMUM', 'nama_asuransi' => 'Umum', 'alamat_asuransi' => '-',
            'tgl_tarikan' => $tglTarikan, 'menu' => $menu, 'tgl_faktur' => '2026-03-05',
        ]);

        $smc->table('faktur_pajak_ditarik_detail')->insert([
            'no_rawat' => $noRawat, 'kode_transaksi' => '04', 'tgl_bayar' => '2026-03-05',
            'jam_bayar' => '09:00:00', 'tgl_tarikan' => $tglTarikan, 'menu' => $menu,
            'jenis_barang_jasa' => 'A', 'kode_barang_jasa' => 'BRG-'.$noRawat, 'nama_barang_jasa' => 'Jasa Uji',
            'nama_satuan_ukur' => 'UM.0033', 'kd_jenis_prw' => 'RJ', 'kategori' => 'Registrasi',
            'status_lanjut' => 'Ralan', 'kode_asuransi' => 'UMUM', 'no_rkm_medis' => 'RM-'.$noRawat,
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanFakturPajakUmum::class);
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
     *
     * Once a tarikan is picked, the header list reads straight from
     * faktur_pajak_ditarik scoped to this page's own menu - a row pulled by a
     * different report (fp-bpjs) sharing the same tanggal must not show up.
     */
    public function shows_only_this_menus_rows_for_the_selected_tarikan(): void
    {
        $this->tarikan(self::MENU, 'UJI/001', '2026-03-05 10:00:00');
        $this->tarikan('fp-bpjs', 'UJI/002', '2026-03-05 10:00:00');

        $test = $this->report()
            ->set('tanggalTarikan', '2026-03-05 10:00:00')
            ->call('loadProperties');

        $this->assertSame(
            ['UJI/001'],
            $test->instance()->dataLaporanFakturPajak->pluck('no_rawat')->all()
        );
    }

    /**
     * @test
     *
     * Same isolation, for the detail tab and for the tarikan-date picker
     * itself - a date that only has fp-bpjs rows under it must not appear as
     * a selectable tarikan on the Umum page.
     */
    public function detail_tab_and_tarikan_picker_are_also_scoped_to_this_menu(): void
    {
        $this->tarikan(self::MENU, 'UJI/001', '2026-03-05 10:00:00');
        $this->tarikan('fp-bpjs', 'UJI/002', '2026-03-06 10:00:00');

        $test = $this->report()
            ->set('tanggalTarikan', '2026-03-05 10:00:00')
            ->call('loadProperties');

        $this->assertSame(
            ['UJI/001'],
            $test->instance()->dataDetailFakturPajak->pluck('no_rawat')->all()
        );
        $this->assertSame(
            ['2026-03-05 10:00:00' => '2026-03-05 10:00:00'],
            $test->instance()->dataTanggalTarikan->all()
        );
    }

    /**
     * @test
     *
     * FakturPajakDitarik previously had no $searchColumns at all, so typing
     * into the search box while browsing any already-pulled tarikan crashed
     * with "No columns are defined to perform search." - fixed by adding
     * searchColumns to the model. Mutation-tested: reverting the fix
     * reproduces the crash on this exact test.
     *
     * Note: this only narrows the "Faktur" (header) tab - the "Detail Faktur"
     * tab's historical-view query never chains ->search() at all, so the
     * search box has no effect there once a tarikan has been pulled. That
     * asymmetry is pre-existing and left as-is here.
     */
    public function search_narrows_a_pulled_tarikan_without_crashing(): void
    {
        $this->tarikan(self::MENU, 'UJI/001', '2026-03-05 10:00:00');
        $this->tarikan(self::MENU, 'UJI/002', '2026-03-05 10:00:00');

        $test = $this->report()
            ->set('tanggalTarikan', '2026-03-05 10:00:00')
            ->call('loadProperties')
            ->set('cari', '001');

        $this->assertSame(
            ['UJI/001'],
            $test->instance()->dataLaporanFakturPajak->pluck('no_rawat')->all()
        );
    }
}
