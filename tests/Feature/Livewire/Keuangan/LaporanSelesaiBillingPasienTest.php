<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanSelesaiBillingPasien;
use App\Models\Keuangan\NotaSelesai;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: pulling "billing selesai" entries out of mysql_sik's raw
 * jurnal.keterangan text into the local nota_selesai cache table
 * tarikDataTerbaru() reads from.
 *
 * NotaSelesai::refreshModel() is entirely local and only touches
 * mysql_sik.jurnal directly (unlike billingYangDiselesaikan(), the display
 * scope, which joins across half a dozen other tables and gets only a
 * mount-level smoke check here) - same shape as JurnalSupplierPOTest. The
 * parsing is the interesting part: bentuk_bayar is the first word before
 * "PASIEN", status_pasien the two words after it, and no_rawat is
 * reconstructed by pulling every run of digits out of the whole sentence and
 * taking the first four - fragile by construction, so the fixture keeps
 * digits out of every other part of the keterangan text.
 */
class LaporanSelesaiBillingPasienTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-selesai-billing.read';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $smc->table('nota_selesai')->where('no_rawat', 'like', '2026/%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $keterangan): void
    {
        DB::connection('mysql_sik')->table('jurnal')->insert([
            'no_jurnal' => $noJurnal, 'no_bukti' => 'BUKTI-'.$noJurnal, 'tgl_jurnal' => '2026-03-05',
            'jam_jurnal' => '08:00:00', 'jenis' => 'U', 'keterangan' => $keterangan,
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(LaporanSelesaiBillingPasien::class);
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
     * "PEMBAYARAN PASIEN RAWAT JALAN..." -> bentuk_bayar "PEMBAYARAN",
     * status_pasien "RAWAT JALAN", no_rawat rebuilt from the four digit runs
     * in the sentence, petugas the name after the last "OLEH ".
     */
    public function pulls_a_rawat_jalan_pembayaran_entry_and_parses_it_correctly(): void
    {
        $this->jurnal('UJI-1', 'PEMBAYARAN PASIEN RAWAT JALAN NO. RAWAT 2026/03/05/0001 TELAH DIPOSTING OLEH Petugas Uji');

        $this->report()->call('tarikDataTerbaru');

        $row = NotaSelesai::where('no_rawat', '2026/03/05/0001')->first();

        $this->assertNotNull($row);
        $this->assertSame('PEMBAYARAN', $row->bentuk_bayar);
        $this->assertSame('RAWAT JALAN', $row->status_pasien);
        $this->assertSame('Petugas Uji', $row->user_id);
    }

    /**
     * @test
     *
     * "PIUTANG PASIEN RAWAT INAP..." is a distinct matched sentence shape
     * from the pembayaran one above - both bentuk_bayar and status_pasien
     * must reflect it, not the rawat-jalan case's values.
     */
    public function pulls_a_rawat_inap_piutang_entry_and_parses_it_correctly(): void
    {
        $this->jurnal('UJI-2', 'PIUTANG PASIEN RAWAT INAP NO. RAWAT 2026/03/06/0002 TELAH DIPOSTING OLEH Petugas Dua');

        $this->report()->call('tarikDataTerbaru');

        $row = NotaSelesai::where('no_rawat', '2026/03/06/0002')->first();

        $this->assertNotNull($row);
        $this->assertSame('PIUTANG', $row->bentuk_bayar);
        $this->assertSame('RAWAT INAP', $row->status_pasien);
        $this->assertSame('Petugas Dua', $row->user_id);
    }

    /**
     * @test
     */
    public function does_not_pick_up_unrelated_jurnal_entries(): void
    {
        $this->jurnal('UJI-3', 'SETOR TUNAI KE BANK');

        $this->report()->call('tarikDataTerbaru');

        $this->assertSame(0, NotaSelesai::query()->count());
    }
}
