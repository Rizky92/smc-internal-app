<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\BukuBesar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The general ledger, asserted on its arithmetic rather than on it rendering.
 *
 * Every other test in this suite checks that a page serves. This one seeds a
 * ledger it knows the totals of and checks the figures that come back, which is
 * the thing a report is actually for. Jurnal::scopeBukuBesar() joins jurnal to
 * detailjurnal to rekening, filters on a date range and optionally on one
 * account; scopeJumlahDebetKreditBukuBesar() sums the same join. Both are raw
 * SQL, and neither is verified by a page returning 200.
 *
 * Four journals, three inside the period and one outside, with amounts picked so
 * that every total is reachable by exactly one combination of rows — a sum that
 * silently included the out-of-period journal, or double-counted a join, could
 * not produce these numbers by accident.
 */
class BukuBesarTest extends TestCase
{
    private const URI_PERMISSION = 'keuangan.buku-besar.read';

    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function setUp(): void
    {
        parent::setUp();

        // getRekeningProperty() caches the account list for a day.
        Cache::forget('rekening_bukubesar');
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        // Children before parents: detailjurnal points at both jurnal and rekening.
        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    /**
     * @param  list<array{kd_rek: string, debet: float|int, kredit: float|int}>  $detail
     */
    private function jurnal(string $noJurnal, string $tanggal, array $detail): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('jurnal')->insert([
            'no_jurnal'  => $noJurnal,
            'no_bukti'   => 'BUKTI-'.$noJurnal,
            'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '09:00:00',
            'jenis'      => 'U',
            'keterangan' => 'Jurnal '.$noJurnal,
        ]);

        foreach ($detail as $baris) {
            $sik->table('detailjurnal')->insert([
                'no_jurnal' => $noJurnal,
                'kd_rek'    => $baris['kd_rek'],
                'debet'     => $baris['debet'],
                'kredit'    => $baris['kredit'],
            ]);
        }
    }

    private function seedLedger(): void
    {
        $sik = DB::connection('mysql_sik');

        foreach ([['UJI.1', 'Kas Uji'], ['UJI.2', 'Pendapatan Uji']] as [$kode, $nama]) {
            $sik->table('rekening')->insert([
                'kd_rek'  => $kode,
                'nm_rek'  => $nama,
                'tipe'    => 'R',
                'balance' => 'D',
                'level'   => '1',
            ]);
        }

        // Inside the period.
        $this->jurnal('UJI-001', '2026-03-05', [['kd_rek' => 'UJI.1', 'debet' => 150000, 'kredit' => 0]]);
        $this->jurnal('UJI-002', '2026-03-10', [['kd_rek' => 'UJI.2', 'debet' => 0, 'kredit' => 90000]]);
        $this->jurnal('UJI-003', '2026-03-20', [['kd_rek' => 'UJI.1', 'debet' => 250000, 'kredit' => 0]]);

        // Outside it, and deliberately larger than everything else so that
        // including it by mistake could not be mistaken for a rounding error.
        $this->jurnal('UJI-004', '2026-02-15', [['kd_rek' => 'UJI.1', 'debet' => 777000, 'kredit' => 0]]);
    }

    private function ledger(string $kodeRekening = '')
    {
        $petugas = $this->petugasWithPermissions([self::URI_PERMISSION], '99999901');

        return Livewire::actingAs($petugas)
            ->test(BukuBesar::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->set('kodeRekening', $kodeRekening)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * 150.000 + 250.000 debet and 90.000 kredit, from the three journals dated
     * inside March. The February journal contributes nothing.
     */
    public function totals_only_the_journals_inside_the_period(): void
    {
        $this->seedLedger();

        $this->ledger()
            ->assertSee('Rp. 400.000')
            ->assertSee('Rp. 90.000')
            ->assertDontSee('Rp. 777.000');
    }

    /**
     * @test
     */
    public function lists_the_journals_inside_the_period_and_no_others(): void
    {
        $this->seedLedger();

        $this->ledger()
            ->assertSee('UJI-001')
            ->assertSee('UJI-002')
            ->assertSee('UJI-003')
            ->assertDontSee('UJI-004');
    }

    /**
     * @test
     *
     * Narrowing to one account drops UJI-002 entirely, so the kredit total falls
     * to zero while the debet total is unchanged.
     */
    public function narrows_to_a_single_account(): void
    {
        $this->seedLedger();

        $this->ledger('UJI.1')
            ->assertSee('Rp. 400.000')
            ->assertSee('Rp. 0')
            ->assertSee('UJI-001')
            ->assertSee('UJI-003')
            ->assertDontSee('UJI-002');
    }

    /**
     * @test
     *
     * An account with no movement in the period reports zero rather than the
     * whole ledger. ifnull(...) in the sum is what makes this a 0 and not a null.
     */
    public function reports_zero_for_an_account_with_no_movement(): void
    {
        $this->seedLedger();

        $this->ledger('UJI.2')
            ->assertSee('Rp. 90.000')
            ->assertDontSee('Rp. 400.000')
            ->assertDontSee('UJI-001');
    }
}
