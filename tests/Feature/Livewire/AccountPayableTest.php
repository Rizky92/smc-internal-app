<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\AccountPayable;
use App\Models\Aplikasi\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Hutang aging — what the hospital owes its suppliers, by how long it has owed it.
 *
 * The mirror of Account Receivable, and built the same way: buckets from
 * datediff(tglAkhir, titip_faktur.tanggal) at 30, 60 and 90 days, inclusive on
 * the lower side.
 *
 * It has two halves, medical supplies and everything else, each behind its own
 * permission, each with its own model and its own pair of scopes. Only the
 * medical half is covered here — the non-medical one is a different set of
 * tables and deserves its own fixture rather than a copy of this one.
 */
class AccountPayableTest extends TestCase
{
    private const MEDIS = 'keuangan.account-payable.read-medis';

    private const NONMEDIS = 'keuangan.account-payable.read-nonmedis';

    private const AWAL = '2025-12-01';

    private const AKHIR = '2026-03-31';

    /** @var User|null */
    private $petugas;

    /** @var list<string> */
    private $izin = [self::MEDIS];

    private function petugas()
    {
        return $this->petugas ??= $this->petugasWithPermissions($this->izin, '99999901');
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        // Children before parents.
        $sik->table('bayar_pemesanan')->where('no_faktur', 'like', 'UJI%')->delete();
        $sik->table('detail_titip_faktur')->where('no_faktur', 'like', 'UJI%')->delete();
        $sik->table('pemesanan')->where('no_faktur', 'like', 'UJI%')->delete();
        $sik->table('titip_faktur')->where('no_tagihan', 'like', 'UJI%')->delete();
        $sik->table('akun_bayar_hutang')->where('nama_bayar', 'like', 'UJI%')->delete();
        $sik->table('datasuplier')->where('kode_suplier', 'like', 'UJI%')->delete();
        $sik->table('bangsal')->where('kd_bangsal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function referensi(): void
    {
        $sik = DB::connection('mysql_sik');

        if ($sik->table('datasuplier')->where('kode_suplier', 'UJIS')->exists()) {
            return;
        }

        $sik->table('datasuplier')->insert(['kode_suplier' => 'UJIS', 'nama_suplier' => 'Suplier Uji']);
        $sik->table('bangsal')->insert(['kd_bangsal' => 'UJIB', 'nm_bangsal' => 'Bangsal Uji', 'status' => '1']);
        $sik->table('akun_bayar_hutang')->insert(['nama_bayar' => 'UJI BANK', 'kd_rek' => null]);
    }

    /**
     * One unpaid supplier invoice, filed under a tagihan dated $tanggalTagihan.
     */
    private function faktur(string $nomor, string $tanggalTagihan, float $tagihan, float $dibayar = 0): void
    {
        $this->referensi();

        $sik = DB::connection('mysql_sik');
        $noFaktur = 'UJI-FK'.$nomor;
        $noTagihan = 'UJI-TG'.$nomor;
        $nip = $this->petugas()->nik;

        $sik->table('titip_faktur')->insert([
            'no_tagihan' => $noTagihan, 'tanggal' => $tanggalTagihan,
            'nip'        => $nip, 'keterangan' => '-', 'status' => 'Ditagihkan',
        ]);

        $sik->table('pemesanan')->insert([
            'no_faktur'    => $noFaktur, 'no_order' => 'UJI-OR'.$nomor,
            'kode_suplier' => 'UJIS', 'nip' => $nip,
            'tgl_pesan'    => $tanggalTagihan, 'tgl_faktur' => $tanggalTagihan, 'tgl_tempo' => $tanggalTagihan,
            'total1'       => $tagihan, 'potongan' => 0, 'total2' => $tagihan, 'ppn' => 0, 'meterai' => 0,
            'tagihan'      => $tagihan, 'kd_bangsal' => 'UJIB', 'status' => 'Titip Faktur',
        ]);

        $sik->table('detail_titip_faktur')->insert(['no_tagihan' => $noTagihan, 'no_faktur' => $noFaktur]);

        if ($dibayar > 0) {
            $sik->table('bayar_pemesanan')->insert([
                'tgl_bayar'   => $tanggalTagihan, 'no_faktur' => $noFaktur, 'nip' => $nip,
                'besar_bayar' => $dibayar, 'keterangan' => '-',
                'nama_bayar'  => 'UJI BANK', 'no_bukti' => 'UJI-BK'.$nomor,
            ]);
        }
    }

    private function report()
    {
        return Livewire::actingAs($this->petugas())
            ->test(AccountPayable::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * Two invoices per bucket, one on each side of the boundary. Asserted on the
     * computed property rather than the page: the same figure can be a row value
     * and a total at once, and assertSee cannot tell those apart.
     */
    public function sorts_invoices_into_the_right_ageing_bucket(): void
    {
        // datediff from 2026-03-31.
        $this->faktur('01', '2026-03-31', 100000);   //   0 days -> 0-30
        $this->faktur('02', '2026-03-01', 200000);   //  30 days -> 0-30    (boundary)
        $this->faktur('03', '2026-02-28', 400000);   //  31 days -> 31-60   (boundary)
        $this->faktur('04', '2026-01-30', 500000);   //  60 days -> 31-60   (boundary)
        $this->faktur('05', '2026-01-29', 600000);   //  61 days -> 61-90   (boundary)
        $this->faktur('06', '2025-12-31', 700000);   //  90 days -> 61-90   (boundary)
        $this->faktur('07', '2025-12-30', 800000);   //  91 days -> over 90 (boundary)
        $this->faktur('08', '2025-12-02', 50000);    // 119 days -> over 90

        $total = $this->report()->instance()->totalAccountPayableMedis;
        $perPeriode = $total['totalSisaPerPeriode'];

        $this->assertEqualsWithDelta(300000, $perPeriode->get('periode_0_30'), 0.01);
        $this->assertEqualsWithDelta(900000, $perPeriode->get('periode_31_60'), 0.01);
        $this->assertEqualsWithDelta(1300000, $perPeriode->get('periode_61_90'), 0.01);
        $this->assertEqualsWithDelta(850000, $perPeriode->get('periode_90_up'), 0.01);
        $this->assertEqualsWithDelta(3350000, $total['totalSisaTagihan'], 0.01);
    }

    /**
     * @test
     *
     * A part payment leaves the invoice outstanding for the remainder.
     */
    public function subtracts_what_has_been_paid(): void
    {
        $this->faktur('09', '2026-03-10', 1000000, 400000);
        $this->faktur('10', '2026-03-11', 250000, 100000);

        $total = $this->report()->instance()->totalAccountPayableMedis;

        $this->assertEqualsWithDelta(1250000, $total['totalTagihan'], 0.01);
        $this->assertEqualsWithDelta(500000, $total['totalDibayar'], 0.01);
        $this->assertEqualsWithDelta(750000, $total['totalSisaTagihan'], 0.01);
    }

    /**
     * @test
     *
     * bayar_pemesanan.nama_bayar is the name of the account the invoice was paid
     * from — a varchar, and itself a foreign key into akun_bayar_hutang. The
     * column is headed "Akun Bayar" in the table.
     */
    public function shows_the_name_of_the_paying_account(): void
    {
        $this->faktur('11', '2026-03-10', 1000000, 400000);

        $this->report()->assertSee('UJI BANK');
    }

    /**
     * @test
     *
     * The two halves are gated separately. Holding only the non-medical
     * permission must not reveal the medical figures.
     */
    public function keeps_the_two_halves_behind_their_own_permissions(): void
    {
        $this->izin = [self::NONMEDIS];

        $this->faktur('12', '2026-03-10', 1000000);

        $this->assertSame([], $this->report()->instance()->totalAccountPayableMedis);
    }
}
