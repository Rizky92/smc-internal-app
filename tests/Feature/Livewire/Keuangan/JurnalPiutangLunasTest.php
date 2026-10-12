<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\JurnalPiutangLunas;
use App\Models\Keuangan\PiutangDilunaskan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: pulling paid/cancelled receivables ("piutang") out of mysql_sik's
 * raw jurnal.keterangan text into the local piutang_dilunaskan cache table
 * tarikDataTerbaru() reads from.
 *
 * PiutangDilunaskan::refreshModel() is entirely local, same as
 * JurnalSupplierPOTest's JurnalMedis/JurnalNonMedis::refreshModel() - no
 * external system involved. It joins across seven mysql_sik tables (jurnal,
 * detailjurnal, detail_penagihan_piutang, penagihan_piutang,
 * detail_piutang_pasien, akun_piutang, bayar_piutang) to find candidate
 * rows, then a status/verifier are parsed out of the keterangan text in PHP.
 * The fixture below reuses AccountReceivableTest's piutang chain (same
 * tables). The incremental watermark dedup itself is not re-verified here -
 * it is the same "latest cached waktu_jurnal" mechanism already covered for
 * JurnalMedis/JurnalNonMedis in JurnalSupplierPOTest, just reading from a
 * different join.
 */
class JurnalPiutangLunasTest extends TestCase
{
    private const PERMISSION = 'keuangan.jurnal-piutang-lunas.read';

    /** @var \App\Models\Aplikasi\User|null */
    private $petugas;

    private function petugas()
    {
        return $this->petugas ??= $this->petugasWithPermissions([self::PERMISSION], '99999901');
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        foreach (['bayar_piutang', 'detail_piutang_pasien', 'detail_penagihan_piutang'] as $table) {
            $sik->table($table)->where('no_rawat', 'like', 'UJI%')->delete();
        }
        $sik->table('penagihan_piutang')->where('no_tagihan', 'like', 'UJI%')->delete();
        $sik->table('akun_piutang')->where('nama_bayar', 'like', 'UJI%')->delete();
        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();
        $smc->table('piutang_dilunaskan')->where('no_jurnal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function akun(): void
    {
        $sik = DB::connection('mysql_sik');

        if ($sik->table('rekening')->where('kd_rek', 'UJI.9')->exists()) {
            return;
        }

        $sik->table('rekening')->insert([
            'kd_rek' => 'UJI.9', 'nm_rek' => 'Piutang Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);
        $sik->table('akun_piutang')->insert([
            'nama_bayar' => 'UJI-BAYAR', 'kd_rek' => 'UJI.9',
        ]);
    }

    /**
     * One paid-off receivable, its jurnal entry carrying $keterangan.
     */
    private function piutangDilunaskan(string $nomor, string $noJurnal, string $keterangan): void
    {
        $this->akun();

        $sik = DB::connection('mysql_sik');
        $noRawat = 'UJI/'.$nomor;
        $noRekamMedis = 'UJI-RM'.$nomor;
        $kodePenjamin = $sik->table('penjab')->value('kd_pj');
        $tanggal = '2026-03-05';

        $this->createPasien($noRekamMedis, 'Pasien '.$nomor);
        $this->createRegistrasi($noRawat, $noRekamMedis, $tanggal);

        $sik->table('penagihan_piutang')->insert([
            'no_tagihan' => 'UJI-TAG'.$nomor, 'tanggal' => $tanggal, 'tanggaltempo' => $tanggal, 'tempo' => 30,
            'nip' => $this->petugas()->nik, 'nip_menyetujui' => $this->petugas()->nik, 'kd_pj' => $kodePenjamin,
            'catatan' => '-', 'kd_rek' => 'UJI.9', 'status' => 'Sudah Dibayar',
        ]);

        $sik->table('detail_penagihan_piutang')->insert([
            'no_tagihan' => 'UJI-TAG'.$nomor, 'no_rawat' => $noRawat, 'sisapiutang' => 0, 'diskon' => 0,
        ]);

        $sik->table('detail_piutang_pasien')->insert([
            'no_rawat' => $noRawat, 'nama_bayar' => 'UJI-BAYAR', 'kd_pj' => $kodePenjamin,
            'totalpiutang' => 100000, 'sisapiutang' => 0, 'tgltempo' => $tanggal,
        ]);

        $sik->table('bayar_piutang')->insert([
            'tgl_bayar' => $tanggal, 'no_rkm_medis' => $noRekamMedis, 'no_rawat' => $noRawat,
            'besar_cicilan' => 100000, 'catatan' => '-',
            'kd_rek' => 'UJI.9', 'kd_rek_kontra' => 'UJI.9',
            'diskon_piutang' => 0, 'kd_rek_diskon_piutang' => 'UJI.9',
            'tidak_terbayar' => 0, 'kd_rek_tidak_terbayar' => 'UJI.9',
        ]);

        $sik->table('jurnal')->insert([
            'no_jurnal' => $noJurnal, 'no_bukti' => $noRawat, 'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '10:00:00', 'jenis' => 'U', 'keterangan' => $keterangan,
        ]);

        $sik->table('detailjurnal')->insert([
            'no_jurnal' => $noJurnal, 'kd_rek' => 'UJI.9', 'debet' => 0, 'kredit' => 100000,
        ]);
    }

    private function report()
    {
        return Livewire::actingAs($this->petugas())->test(JurnalPiutangLunas::class);
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
     * A "BAYAR..." keterangan is a completed payment - status stays "Bayar"
     * and the verifier is the name after the last "OLEH ".
     */
    public function pulls_a_paid_receivable_and_extracts_its_verifier(): void
    {
        $this->piutangDilunaskan('01', 'UJI-J01', 'BAYAR PIUTANG TAGIHAN NO.TAGIHAN UJI-TAG01, OLEH Petugas Verifikasi');

        $this->report()->call('tarikDataTerbaru');

        $row = PiutangDilunaskan::where('no_jurnal', 'UJI-J01')->first();

        $this->assertNotNull($row);
        $this->assertSame('Bayar', $row->status);
        $this->assertSame('Petugas Verifikasi', $row->nik_validasi);
    }

    /**
     * @test
     *
     * A "PEMBATALAN BAYAR..." keterangan does not start with "BAYAR", so
     * refreshModel() must record it as a cancelled payment instead.
     */
    public function marks_a_cancelled_payment_as_batal_bayar(): void
    {
        $this->piutangDilunaskan('02', 'UJI-J02', 'PEMBATALAN BAYAR PIUTANG TAGIHAN NO.TAGIHAN UJI-TAG02, OLEH Petugas Pembatal');

        $this->report()->call('tarikDataTerbaru');

        $row = PiutangDilunaskan::where('no_jurnal', 'UJI-J02')->first();

        $this->assertNotNull($row);
        $this->assertSame('Batal Bayar', $row->status);
        $this->assertSame('Petugas Pembatal', $row->nik_validasi);
    }
}
