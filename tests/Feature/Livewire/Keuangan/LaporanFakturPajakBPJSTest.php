<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanFakturPajakBPJS;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the pulled ("tarikan") tax-invoice snapshot for BPJS KESEHATAN
 * patients, once it has already been materialized.
 *
 * Same shape as LaporanFakturPajakUmumTest - see that class's docblock for
 * why simpanTarikan()/updateHargaObat()'s live billing-join computation is
 * out of scope here, and for the two findings shared by all three
 * faktur-pajak reports (a single read permission also gates the tarikan
 * write, and simpanTarikan()'s multi-step insert isn't wrapped in a
 * transaction). The searchColumns fix on FakturPajakDitarik is exercised
 * again here since this page filters that same model by a different menu.
 */
class LaporanFakturPajakBPJSTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-faktur-pajak.read';

    private const MENU = 'fp-bpjs';

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
            'jam_bayar' => '09:00:00', 'status_lanjut' => 'Ranap', 'id_tku_penjual' => '0000000000000000',
            'no_rkm_medis' => 'RM-'.$noRawat, 'nik_pasien' => '1234567890123456',
            'nama_pasien' => 'Pasien '.$noRawat, 'alamat_pasien' => 'Alamat Uji',
            'kode_asuransi' => 'BPJ', 'nama_asuransi' => 'BPJS Kesehatan', 'alamat_asuransi' => '-',
            'tgl_tarikan' => $tglTarikan, 'menu' => $menu, 'tgl_faktur' => '2026-03-05',
        ]);

        $smc->table('faktur_pajak_ditarik_detail')->insert([
            'no_rawat' => $noRawat, 'kode_transaksi' => '04', 'tgl_bayar' => '2026-03-05',
            'jam_bayar' => '09:00:00', 'tgl_tarikan' => $tglTarikan, 'menu' => $menu,
            'jenis_barang_jasa' => 'A', 'kode_barang_jasa' => 'BRG-'.$noRawat, 'nama_barang_jasa' => 'Jasa Uji',
            'nama_satuan_ukur' => 'UM.0033', 'kd_jenis_prw' => 'RI', 'kategori' => 'Kamar Inap',
            'status_lanjut' => 'Ranap', 'kode_asuransi' => 'BPJ', 'no_rkm_medis' => 'RM-'.$noRawat,
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanFakturPajakBPJS::class);
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
     * A row pulled by a different report (fp-umum) sharing the same tanggal
     * must not leak into the BPJS page's own tarikan view.
     */
    public function shows_only_this_menus_rows_for_the_selected_tarikan(): void
    {
        $this->tarikan(self::MENU, 'UJI/001', '2026-03-05 10:00:00');
        $this->tarikan('fp-umum', 'UJI/002', '2026-03-05 10:00:00');

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
     */
    public function detail_tab_and_tarikan_picker_are_also_scoped_to_this_menu(): void
    {
        $this->tarikan(self::MENU, 'UJI/001', '2026-03-05 10:00:00');
        $this->tarikan('fp-umum', 'UJI/002', '2026-03-06 10:00:00');

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
     * See LaporanFakturPajakUmumTest::search_narrows_a_pulled_tarikan_without_crashing()
     * for the bug this reproduces (FakturPajakDitarik had no searchColumns at
     * all) and the note on the Detail Faktur tab not being affected by search.
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
