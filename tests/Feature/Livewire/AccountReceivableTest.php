<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\AccountReceivable;
use App\Models\Aplikasi\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
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
}
