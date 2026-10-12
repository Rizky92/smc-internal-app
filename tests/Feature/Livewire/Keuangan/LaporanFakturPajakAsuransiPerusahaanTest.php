<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanFakturPajakAsuransiPerusahaan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the pulled ("tarikan") tax-invoice snapshot for ASURANSI/PERUSAHAAN
 * patients, once it has already been materialized, plus the "Penjamin"
 * (payer) picker used to pre-filter a fresh tarikan.
 *
 * Same shape as LaporanFakturPajakUmumTest - see that class's docblock for
 * why simpanTarikan()/updateHargaObat()'s live billing-join computation is
 * out of scope here, and for the two findings shared by all three
 * faktur-pajak reports (a single read permission also gates the tarikan
 * write, and simpanTarikan()'s multi-step insert isn't wrapped in a
 * transaction).
 *
 * Also shared: simpanTarikan() used to end with $this->forgetComputed(...), a
 * removed Livewire v2 method - see LaporanFakturPajakUmumTest's docblock.
 * exports_with_no_matching_billing_data_without_crashing() below exercises
 * that fix on this page too.
 */
class LaporanFakturPajakAsuransiPerusahaanTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-faktur-pajak.read';

    private const MENU = 'fp-asper';

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');
        $sik = DB::connection('mysql_sik');

        $smc->table('faktur_pajak_ditarik_detail')->where('no_rawat', 'like', 'UJI%')->delete();
        $smc->table('faktur_pajak_ditarik')->where('no_rawat', 'like', 'UJI%')->delete();
        $sik->table('perusahaan_pasien')->where('kode_perusahaan', 'UJI')->delete();
        $sik->table('penjab')->where('kd_pj', 'UJI')->delete();

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
            'kode_asuransi' => 'UJI', 'nama_asuransi' => 'Asuransi Uji', 'alamat_asuransi' => '-',
            'tgl_tarikan' => $tglTarikan, 'menu' => $menu, 'tgl_faktur' => '2026-03-05',
        ]);

        $smc->table('faktur_pajak_ditarik_detail')->insert([
            'no_rawat' => $noRawat, 'kode_transaksi' => '04', 'tgl_bayar' => '2026-03-05',
            'jam_bayar' => '09:00:00', 'tgl_tarikan' => $tglTarikan, 'menu' => $menu,
            'jenis_barang_jasa' => 'A', 'kode_barang_jasa' => 'BRG-'.$noRawat, 'nama_barang_jasa' => 'Jasa Uji',
            'nama_satuan_ukur' => 'UM.0033', 'kd_jenis_prw' => 'RI', 'kategori' => 'Kamar Inap',
            'status_lanjut' => 'Ranap', 'kode_asuransi' => 'UJI', 'no_rkm_medis' => 'RM-'.$noRawat,
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanFakturPajakAsuransiPerusahaan::class);
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
     * A row pulled by a different report (fp-bpjs) sharing the same tanggal
     * must not leak into this page's own tarikan view.
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

    /**
     * @test
     *
     * See LaporanFakturPajakUmumTest::exports_with_no_matching_billing_data_without_crashing().
     */
    public function exports_with_no_matching_billing_data_without_crashing(): void
    {
        $this->report()
            ->call('beginExcelExport')
            ->assertFileDownloaded();
    }

    private function penjamin(): void
    {
        DB::connection('mysql_sik')->table('penjab')->insert([
            'kd_pj' => 'UJI', 'png_jawab' => 'Penjamin Uji', 'nama_perusahaan' => '-',
            'alamat_asuransi' => '-', 'no_telp' => '-', 'attn' => '-', 'status' => '1',
        ]);
    }

    /**
     * @test
     *
     * getDataPenjaminProperty() lists every payer when isPerusahaan is off -
     * the default state a fresh tarikan starts from.
     */
    public function penjamin_picker_lists_every_payer_by_default(): void
    {
        $this->penjamin();

        $this->assertArrayHasKey(
            'UJI',
            $this->report()->instance()->dataPenjamin->all()
        );
    }

    /**
     * @test
     *
     * Toggled on, it narrows to payers backed by a registered
     * perusahaan_pasien row - the switch a fresh tarikan uses to decide
     * whether it's pulling insurance-company or corporate-client invoices. A
     * payer with no matching company must drop out...
     */
    public function penjamin_picker_drops_payers_without_a_registered_company_when_toggled(): void
    {
        $this->penjamin();

        $this->assertArrayNotHasKey(
            'UJI',
            $this->report()->set('isPerusahaan', true)->instance()->dataPenjamin->all()
        );
    }

    /**
     * @test
     *
     * ...and reappear once one exists.
     */
    public function penjamin_picker_keeps_payers_with_a_registered_company_when_toggled(): void
    {
        $this->penjamin();

        DB::connection('mysql_sik')->table('perusahaan_pasien')->insert([
            'kode_perusahaan' => 'UJI', 'nama_perusahaan' => 'PT Uji',
        ]);

        $this->assertArrayHasKey(
            'UJI',
            $this->report()->set('isPerusahaan', true)->instance()->dataPenjamin->all()
        );
    }
}
