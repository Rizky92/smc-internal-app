<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Jobs\Keuangan\BayarPiutangPasien;
use App\Livewire\Pages\Keuangan\AccountReceivable;
use App\Models\Aplikasi\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Piutang aging — what patients still owe, sorted by how long they have owed it.
 *
 * Two pieces of arithmetic, both in raw SQL and neither exercised by the page
 * returning 200.
 *
 * The ageing buckets come from datediff(tglAkhir, penagihan_piutang.tanggal),
 * split at 30, 60 and 90 days. Those are inclusive boundaries — `<= 30`,
 * `between 31 and 60`, `between 61 and 90`, `> 90` — so an invoice exactly 30, 60
 * or 90 days old belongs to the younger bucket, and one day more moves it. Every
 * boundary is covered here, in both directions.
 *
 * The balance is a four-term subtraction:
 *
 *     sisa = totalpiutang - besar_cicilan - diskon_piutang - tidak_terbayar
 *
 * Dropping any one term still produces a plausible number.
 *
 * validasiPiutang() is also covered here rather than on its own page,
 * Keuangan/ValidasiPiutang.php - that component is dead scaffolding (no
 * route, no <livewire:...> reference anywhere; grepped to confirm), and the
 * actual "validate receivable" action lives on this component instead. It
 * only dispatches a BayarPiutangPasien job per selected row - the job's own
 * write pipeline (jurnal postings, piutang balances, etc.) is a separate,
 * much larger unit and out of scope here.
 */
class AccountReceivableTest extends TestCase
{
    private const PERMISSION = 'keuangan.account-receivable.read';

    private const AWAL = '2025-12-01';

    private const AKHIR = '2026-03-31';

    /** @var User|null */
    private $petugas;

    /**
     * penagihan_piutang.nip is a foreign key into pegawai.nik, so the member of
     * staff who raised the invoice has to exist before the invoice does. The same
     * one signs in to read the report.
     */
    private function petugas()
    {
        return $this->petugas ??= $this->petugasWithPermissions([self::PERMISSION], '99999901');
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        // Children before parents, and each table matched on the column it
        // actually has: penagihan_piutang carries no no_rawat, bayar_piutang no
        // no_tagihan. These have to go before TestCase::tearDown() removes the
        // registrations they point at.
        foreach (['bayar_piutang', 'detail_piutang_pasien', 'piutang_pasien', 'detail_penagihan_piutang'] as $table) {
            $sik->table($table)->where('no_rawat', 'like', 'UJI%')->delete();
        }

        $sik->table('penagihan_piutang')->where('no_tagihan', 'like', 'UJI%')->delete();

        $sik->table('akun_penagihan_piutang')->where('kd_rek', 'like', 'UJI%')->delete();
        $sik->table('akun_piutang')->where('nama_bayar', 'like', 'UJI%')->delete();
        $sik->table('akun_bayar')->where('nama_bayar', 'like', 'UJI%')->delete();
        $sik->table('set_akun')->where('Diskon_Piutang', 'UJI.DISKON')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

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
        $sik->table('akun_penagihan_piutang')->insert([
            'kd_rek' => 'UJI.9', 'nama_bank' => 'Bank Uji', 'atas_nama' => 'RS Uji', 'no_rek' => '000',
        ]);
        $sik->table('akun_piutang')->insert([
            'nama_bayar' => 'UJI-BAYAR', 'kd_rek' => 'UJI.9',
        ]);
    }

    /**
     * One outstanding invoice, dated $tanggalTagihan, for $piutang.
     */
    private function tagihan(
        string $nomor,
        string $tanggalTagihan,
        float $piutang,
        float $cicilan = 0,
        float $diskon = 0,
        float $tidakTerbayar = 0
    ): void {
        $this->akun();

        $sik = DB::connection('mysql_sik');
        $noRawat = 'UJI/'.$nomor;
        $noRekamMedis = 'UJI-RM'.$nomor;
        $kodePenjamin = $sik->table('penjab')->value('kd_pj');

        $this->createPasien($noRekamMedis, 'Pasien '.$nomor);
        $this->createRegistrasi($noRawat, $noRekamMedis, $tanggalTagihan);

        $sisa = $piutang - $cicilan - $diskon - $tidakTerbayar;

        $sik->table('penagihan_piutang')->insert([
            'no_tagihan'   => 'UJI-TAG'.$nomor, 'tanggal' => $tanggalTagihan,
            'tanggaltempo' => $tanggalTagihan, 'tempo' => 30,
            'nip'          => $this->petugas()->nik, 'nip_menyetujui' => $this->petugas()->nik, 'kd_pj' => $kodePenjamin,
            'catatan'      => '-', 'kd_rek' => 'UJI.9', 'status' => 'Proses Penagihan',
        ]);

        $sik->table('detail_penagihan_piutang')->insert([
            'no_tagihan' => 'UJI-TAG'.$nomor, 'no_rawat' => $noRawat, 'sisapiutang' => $piutang, 'diskon' => 0,
        ]);

        $sik->table('piutang_pasien')->insert([
            'no_rawat'    => $noRawat, 'tgl_piutang' => $tanggalTagihan, 'no_rkm_medis' => $noRekamMedis,
            'status'      => 'Belum Lunas', 'totalpiutang' => $piutang, 'uangmuka' => 0,
            'sisapiutang' => $sisa, 'tgltempo' => $tanggalTagihan,
        ]);

        $sik->table('detail_piutang_pasien')->insert([
            'no_rawat'     => $noRawat, 'nama_bayar' => 'UJI-BAYAR', 'kd_pj' => $kodePenjamin,
            'totalpiutang' => $piutang, 'sisapiutang' => $sisa, 'tgltempo' => $tanggalTagihan,
        ]);

        if ($cicilan > 0 || $diskon > 0 || $tidakTerbayar > 0) {
            $sik->table('bayar_piutang')->insert([
                'tgl_bayar'      => $tanggalTagihan, 'no_rkm_medis' => $noRekamMedis, 'no_rawat' => $noRawat,
                'besar_cicilan'  => $cicilan, 'catatan' => '-',
                'kd_rek'         => 'UJI.9', 'kd_rek_kontra' => 'UJI.9',
                'diskon_piutang' => $diskon, 'kd_rek_diskon_piutang' => 'UJI.9',
                'tidak_terbayar' => $tidakTerbayar, 'kd_rek_tidak_terbayar' => 'UJI.9',
            ]);
        }
    }

    private function report()
    {
        return Livewire::actingAs($this->petugas())
            ->test(AccountReceivable::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * Each bucket gets two invoices, one on each side of its boundary, and the
     * totals are chosen so no bucket total equals any single invoice.
     */
    public function sorts_invoices_into_the_right_ageing_bucket(): void
    {
        // datediff from 2026-03-31.
        $this->tagihan('01', '2026-03-31', 100000);   //  0 days -> 0-30
        $this->tagihan('02', '2026-03-01', 200000);   // 30 days -> 0-30      (boundary)
        $this->tagihan('03', '2026-02-28', 400000);   // 31 days -> 31-60     (boundary)
        $this->tagihan('04', '2026-01-30', 500000);   // 60 days -> 31-60     (boundary)
        $this->tagihan('05', '2026-01-29', 600000);   // 61 days -> 61-90     (boundary)
        $this->tagihan('06', '2025-12-31', 700000);   // 90 days -> 61-90     (boundary)
        $this->tagihan('07', '2025-12-30', 800000);   // 91 days -> over 90   (boundary)
        $this->tagihan('08', '2025-12-02', 50000);    // 119 days -> over 90

        $component = $this->report();

        $perPeriode = $component->instance()->dataTotalAccountReceivable['totalSisaPerPeriode'];

        $this->assertEqualsWithDelta(300000, $perPeriode->get('periode_0_30'), 0.01);
        $this->assertEqualsWithDelta(900000, $perPeriode->get('periode_31_60'), 0.01);
        $this->assertEqualsWithDelta(1300000, $perPeriode->get('periode_61_90'), 0.01);
        $this->assertEqualsWithDelta(850000, $perPeriode->get('periode_90_up'), 0.01);

        // And that those figures actually reach the page, which is what the int
        // cast on `periode` used to prevent.
        $component->assertSee('Rp. 1.300.000');
    }

    /**
     * @test
     *
     * Two invoices, because the balance is computed twice over — once per row by
     * scopeAccountReceivable and once in total by scopeTotalAccountReceivable.
     * With one invoice the two answers are the same figure, and a term dropped
     * from the total still matched the row. Mutation testing caught that.
     *
     *   A  1.000.000 owed, 300.000 paid,  50.000 discount, 20.000 written off -> 630.000
     *   B    450.000 owed, 120.000 paid,  20.000 discount, 10.000 written off -> 300.000
     *
     *   totals: owed 1.450.000, paid 420.000, outstanding 930.000
     */
    public function subtracts_instalments_discounts_and_write_offs(): void
    {
        $this->tagihan('09', '2026-03-10', 1000000, 300000, 50000, 20000);
        $this->tagihan('10', '2026-03-11', 450000, 120000, 20000, 10000);

        // Read off the computed property rather than the rendered page. assertSee
        // is satisfied by a number appearing anywhere, and on this report the same
        // figure can be a row value and a total at once — a mutation that broke
        // the total was matched by an unrelated row and went undetected. Naming
        // the key says which number is meant.
        $total = $this->report()->instance()->dataTotalAccountReceivable;

        $this->assertEqualsWithDelta(1450000, $total['totalPiutang'], 0.01);
        $this->assertEqualsWithDelta(420000, $total['totalCicilan'], 0.01);
        $this->assertEqualsWithDelta(930000, $total['totalSisaCicilan'], 0.01);
    }

    /**
     * Seeds the two mysql_sik config rows validasiPiutang() reads its account
     * codes from - akun_bayar (the payment account picker) and set_akun (the
     * single-row app-wide config for discount/write-off contra accounts).
     */
    private function akunValidasi(): void
    {
        $sik = DB::connection('mysql_sik');

        foreach (['UJI.9', 'UJI.DISKON', 'UJI.TT'] as $kdRek) {
            if (! $sik->table('rekening')->where('kd_rek', $kdRek)->exists()) {
                $sik->table('rekening')->insert([
                    'kd_rek' => $kdRek, 'nm_rek' => 'Akun Uji '.$kdRek, 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
                ]);
            }
        }

        $sik->table('akun_bayar')->insert(['nama_bayar' => 'UJI-BAYAR-VALIDASI', 'kd_rek' => 'UJI.9']);

        // set_akun is a single-row, all-NOT-NULL config table, every column an
        // FK into rekening - unrelated to this test beyond the two columns
        // validasiPiutang() actually reads, so the rest just point at the same
        // placeholder account to satisfy the schema.
        $sik->table('set_akun')->insert(array_merge(
            array_fill_keys([
                'Stok_Keluar_Medis', 'Kontra_Stok_Keluar_Medis', 'Penerimaan_NonMedis',
                'Kontra_Penerimaan_NonMedis', 'Bayar_Pemesanan_Non_Medis', 'Hibah_Obat',
                'Kontra_Hibah_Obat', 'Pengadaan_Toko', 'Kerugian_Klaim_BPJS_RVP',
                'Lebih_Bayar_Klaim_BPJS_RVP', 'Piutang_BPJS_RVP', 'Kontra_Penerimaan_AsetInventaris',
                'Kontra_Hibah_Aset', 'Hibah_Non_Medis', 'Kontra_Hibah_Non_Medis', 'Beban_Hutang_Lain',
                'PPN_Masukan', 'Pengadaan_Dapur', 'Stok_Keluar_Dapur', 'Kontra_Stok_Keluar_Dapur',
                'PPN_Keluaran', 'Lebih_Bayar_Piutang',
            ], 'UJI.9'),
            ['Diskon_Piutang' => 'UJI.DISKON', 'Piutang_Tidak_Terbayar' => 'UJI.TT']
        ));
    }

    private function jobProperty(object $job, string $property)
    {
        $ref = new ReflectionProperty($job, $property);
        $ref->setAccessible(true);

        return $ref->getValue($job);
    }

    /**
     * @test
     */
    public function validasi_piutang_refuses_without_permission(): void
    {
        Queue::fake();

        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        Livewire::actingAs($petugas)
            ->test(AccountReceivable::class)
            ->set('rekeningAkun', 'UJI-BAYAR-VALIDASI')
            ->set('tagihanDipilih', ['TAG01_PJ1_RWT01' => ['selected' => true, 'diskon_piutang' => 0]])
            ->call('validasiPiutang')
            ->assertSee('Anda tidak diizinkan untuk melakukan tindakan ini!');

        Queue::assertNotPushed(BayarPiutangPasien::class);
    }

    /**
     * @test
     */
    public function validasi_piutang_refuses_without_an_akun_pembayaran_selected(): void
    {
        Queue::fake();

        $petugas = $this->petugasWithPermissions(
            [self::PERMISSION, 'keuangan.account-receivable.validasi-piutang'],
            '99999901'
        );

        Livewire::actingAs($petugas)
            ->test(AccountReceivable::class)
            ->set('tagihanDipilih', ['TAG01_PJ1_RWT01' => ['selected' => true, 'diskon_piutang' => 0]])
            ->call('validasiPiutang')
            ->assertSee('Silahkan pilih "Akun Pembayaran" terlebih dahulu!');

        Queue::assertNotPushed(BayarPiutangPasien::class);
    }

    /**
     * @test
     *
     * Two rows in tagihanDipilih, only one marked selected - only the
     * selected one is dispatched, and its key ("no_tagihan_kd_pj_no_rawat")
     * and discount are threaded through to the job unchanged.
     */
    public function validasi_piutang_dispatches_a_job_per_selected_tagihan_only(): void
    {
        Queue::fake();
        $this->akunValidasi();

        $petugas = $this->petugasWithPermissions(
            [self::PERMISSION, 'keuangan.account-receivable.validasi-piutang'],
            '99999901'
        );

        Livewire::actingAs($petugas)
            ->test(AccountReceivable::class)
            ->set('rekeningAkun', 'UJI-BAYAR-VALIDASI')
            ->set('tglBayar', '2026-03-05')
            ->set('tagihanDipilih', [
                'TAG01_PJ1_RWT01' => ['selected' => true, 'diskon_piutang' => 5000],
                'TAG02_PJ1_RWT02' => ['selected' => false, 'diskon_piutang' => 0],
            ])
            ->call('validasiPiutang')
            ->assertSee('Validasi piutang sedang diproses!');

        Queue::assertPushed(BayarPiutangPasien::class, 1);

        Queue::assertPushed(function (BayarPiutangPasien $job) use ($petugas) {
            return $this->jobProperty($job, 'noTagihan') === 'TAG01'
                && $this->jobProperty($job, 'kodePJ') === 'PJ1'
                && $this->jobProperty($job, 'noRawat') === 'RWT01'
                && $this->jobProperty($job, 'userId') === $petugas->nik
                && $this->jobProperty($job, 'tglBayar') === '2026-03-05'
                && $this->jobProperty($job, 'akunBayar') === 'UJI.9'
                && $this->jobProperty($job, 'diskonPiutang') === 5000.0
                && $this->jobProperty($job, 'akunDiskonPiutang') === 'UJI.DISKON'
                && $this->jobProperty($job, 'akunTidakTerbayar') === 'UJI.TT';
        });
    }

    /**
     * @test
     *
     * The selection and running total are cleared once validation is kicked
     * off, so a second click without reselecting anything dispatches nothing.
     */
    public function validasi_piutang_clears_the_selection_afterwards(): void
    {
        Queue::fake();
        $this->akunValidasi();

        $petugas = $this->petugasWithPermissions(
            [self::PERMISSION, 'keuangan.account-receivable.validasi-piutang'],
            '99999901'
        );

        $test = Livewire::actingAs($petugas)
            ->test(AccountReceivable::class)
            ->set('rekeningAkun', 'UJI-BAYAR-VALIDASI')
            ->set('tagihanDipilih', ['TAG01_PJ1_RWT01' => ['selected' => true, 'diskon_piutang' => 0]])
            ->call('validasiPiutang');

        $this->assertSame([], $test->instance()->tagihanDipilih);
        $this->assertSame(0, $test->instance()->totalDibayar);
    }
}
