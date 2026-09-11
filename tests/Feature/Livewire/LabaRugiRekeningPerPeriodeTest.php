<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\LabaRugiRekeningPerPeriode;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The profit and loss statement, asserted on its arithmetic.
 *
 * More opinionated than Buku Besar, which only adds up. This report applies a
 * sign convention that depends on the account: a credit-balance account nets
 * kredit minus debet, a debit-balance account nets debet minus kredit, and the
 * profit is income less expenditure. Get either convention backwards and the
 * report still renders, still balances, and reports the wrong sign — which is
 * why a page returning 200 says nothing useful about it.
 *
 * It also blends two queries. Rekening::semuaRekening() lists every revenue
 * account whether or not it moved, hitungDebetKreditPerPeriode() sums the ones
 * that did, and the two are merged on kd_rek so an untouched account still
 * appears at zero.
 *
 * Two accounts on each side, so that no per-account total equals a report total.
 * With one account each they coincide, and a sign flip on the account row still
 * left the report total right — mutation testing caught that, and these figures
 * are the answer to it:
 *
 *   UJI.4  income   kredit 500.000, debet 50.000  ->  450.000
 *   UJI.7  income   kredit 100.000               ->  100.000
 *   UJI.5  expense  debet  200.000, kredit 20.000 ->  180.000
 *   UJI.8  expense  debet   30.000               ->   30.000
 *   UJI.6  income   no movement                  ->        0
 *
 *   total income       600.000 -  50.000 = 550.000
 *   total expenditure  230.000 -  20.000 = 210.000
 *   profit             550.000 - 210.000 = 340.000
 *
 * Every figure is distinct, and every one of them is positive — so a sign
 * flipped anywhere shows up as a "-Rp." that should never appear.
 */
class LabaRugiRekeningPerPeriodeTest extends TestCase
{
    private const PERMISSION = 'keuangan.laba-rugi-rekening.read';

    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal, string $kdRek, float $debet, float $kredit): void
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

        $sik->table('detailjurnal')->insert([
            'no_jurnal' => $noJurnal,
            'kd_rek'    => $kdRek,
            'debet'     => $debet,
            'kredit'    => $kredit,
        ]);
    }

    private function seedLedger(): void
    {
        $sik = DB::connection('mysql_sik');

        foreach ([
            ['UJI.4', 'Pendapatan Uji', 'K'],
            ['UJI.7', 'Pendapatan Lain Uji', 'K'],
            ['UJI.5', 'Beban Uji', 'D'],
            ['UJI.8', 'Beban Lain Uji', 'D'],
            ['UJI.6', 'Pendapatan Kosong Uji', 'K'],
        ] as [$kode, $nama, $balance]) {
            $sik->table('rekening')->insert([
                'kd_rek'  => $kode,
                'nm_rek'  => $nama,
                'tipe'    => 'R',
                'balance' => $balance,
                'level'   => '1',
            ]);
        }

        // Income, a contra entry against it, and a second income account.
        $this->jurnal('UJI-101', '2026-03-05', 'UJI.4', 0, 500000);
        $this->jurnal('UJI-102', '2026-03-10', 'UJI.4', 50000, 0);
        $this->jurnal('UJI-106', '2026-03-12', 'UJI.7', 0, 100000);

        // Expenditure, a contra entry against it, and a second expense account.
        $this->jurnal('UJI-103', '2026-03-15', 'UJI.5', 200000, 0);
        $this->jurnal('UJI-104', '2026-03-20', 'UJI.5', 0, 20000);
        $this->jurnal('UJI-107', '2026-03-22', 'UJI.8', 30000, 0);

        // February, and larger than anything else so that counting it could not
        // be mistaken for a rounding error.
        $this->jurnal('UJI-105', '2026-02-15', 'UJI.4', 0, 999000);
    }

    private function report(string $kodePenjamin = '')
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)
            ->test(LabaRugiRekeningPerPeriode::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->set('kodePenjamin', $kodePenjamin)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * Income nets kredit against debet; expenditure nets the other way. Swapping
     * the two branches would turn 450.000 into -450.000 and still render.
     */
    public function applies_the_sign_convention_of_each_balance_type(): void
    {
        $this->seedLedger();

        $this->report()
            // Per-account, which is where the convention is applied.
            ->assertSee('Rp. 450.000')
            ->assertSee('Rp. 180.000')
            // And the report totals, which are summed independently of the rows,
            // so both have to be checked to pin both pieces of arithmetic.
            ->assertSee('Rp. 550.000')
            ->assertSee('Rp. 210.000')
            // Every expected figure is positive. A negative one means a
            // subtraction has been turned around somewhere.
            ->assertDontSee('-Rp.');
    }

    /**
     * @test
     */
    public function derives_the_profit_from_income_less_expenditure(): void
    {
        $this->seedLedger();

        $this->report()->assertSee('Rp. 340.000');
    }

    /**
     * @test
     */
    public function ignores_journals_dated_outside_the_period(): void
    {
        $this->seedLedger();

        $this->report()
            ->assertDontSee('Rp. 999.000')
            ->assertSee('Rp. 550.000');
    }

    /**
     * @test
     *
     * semuaRekening() is what puts an untouched account on the report at all;
     * hitungDebetKreditPerPeriode() only returns accounts that moved. Losing the
     * merge would drop the account from the statement rather than show it at
     * zero.
     */
    public function lists_a_revenue_account_that_did_not_move(): void
    {
        $this->seedLedger();

        $this->report()->assertSee('Pendapatan Kosong Uji');
    }

    /**
     * @test
     *
     * Narrowing by penjamin joins through reg_periksa on jurnal.no_bukti. No
     * registration matches these journals, so every account falls back to the
     * zero row semuaRekening() supplies, and the profit with it.
     */
    public function narrowing_by_penjamin_drops_journals_with_no_registration(): void
    {
        $this->seedLedger();

        $this->report('UJI-PJ')
            ->assertSee('Pendapatan Uji')
            ->assertDontSee('Rp. 550.000')
            ->assertDontSee('Rp. 210.000')
            ->assertDontSee('Rp. 340.000');
    }
}
