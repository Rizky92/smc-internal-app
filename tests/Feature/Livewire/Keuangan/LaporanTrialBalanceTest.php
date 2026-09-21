<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\LaporanTrialBalance;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The trial balance, asserted on its arithmetic rather than on it rendering.
 *
 * Three things are blended per account: a saldo_awal split across two
 * sources (a per-year opening balance in rekeningtahun, plus every
 * transaction from the start of the year through the end of last month), the
 * period's own debet/kredit totals, and a balance-type-dependent saldo_akhir
 * (debit accounts add debet and subtract kredit; credit accounts do the
 * opposite - the same sign convention BukuBesar/LabaRugiRekeningPerPeriode
 * already pin, applied here to a running balance instead of a simple total).
 *
 * One debit account, one credit account, and one account with no rekeningtahun
 * row and no jurnal at all, so the "untouched account still appears, at a
 * balance of zero" merge is exercised alongside the two that moved. Every
 * figure below is distinct so a wrong join or a flipped sign shows up as the
 * wrong string on the page rather than an accidental match.
 */
class LaporanTrialBalanceTest extends TestCase
{
    private const PERMISSION = 'keuangan.laporan-trial-balance.read';

    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekeningtahun')->where('kd_rek', 'like', 'UJI%')->delete();
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
            ['UJI.1', 'Kas Uji', 'D'],
            ['UJI.2', 'Pendapatan Uji', 'K'],
            ['UJI.3', 'Rekening Uji Tidak Bergerak', 'D'],
        ] as [$kode, $nama, $balance]) {
            $sik->table('rekening')->insert([
                'kd_rek'  => $kode,
                'nm_rek'  => $nama,
                'tipe'    => 'R',
                'balance' => $balance,
                'level'   => '1',
            ]);
        }

        // Opening balance for the year, per rekeningtahun.
        $sik->table('rekeningtahun')->insert(['thn' => 2026, 'kd_rek' => 'UJI.1', 'saldo_awal' => 110000]);
        $sik->table('rekeningtahun')->insert(['thn' => 2026, 'kd_rek' => 'UJI.2', 'saldo_awal' => 0]);

        // January/February movement, which rolls into March's opening balance.
        $this->jurnal('UJI-101', '2026-01-10', 'UJI.1', 40000, 0);
        $this->jurnal('UJI-102', '2026-02-10', 'UJI.2', 0, 45000);

        // March, the period under test.
        $this->jurnal('UJI-103', '2026-03-05', 'UJI.1', 210000, 35000);
        $this->jurnal('UJI-104', '2026-03-12', 'UJI.2', 8000, 260000);

        // April, outside the period, deliberately the largest figure of all so
        // that including it by mistake could not be mistaken for a rounding error.
        $this->jurnal('UJI-105', '2026-04-01', 'UJI.1', 999000, 0);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)
            ->test(LaporanTrialBalance::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties');
    }

    /**
     * @test
     *
     * UJI.1 (debit): opening 110.000 + Jan/Feb movement 40.000 = 150.000
     * saldo awal; +210.000 debet -35.000 kredit in March = 325.000 saldo akhir.
     */
    public function computes_saldo_awal_and_saldo_akhir_for_a_debit_account(): void
    {
        $this->seedLedger();

        $this->report()
            ->assertSee('150.000,00')
            ->assertSee('210.000,00')
            ->assertSee('35.000,00')
            ->assertSee('325.000,00');
    }

    /**
     * @test
     *
     * UJI.2 (credit): opening 0 + Jan/Feb movement 45.000 = 45.000 saldo awal;
     * the sign convention reverses here - +260.000 kredit -8.000 debet in
     * March = 297.000 saldo akhir. Getting the D/K branch backwards would
     * turn this into a debit-style calculation and produce a different figure.
     */
    public function computes_saldo_awal_and_saldo_akhir_for_a_credit_account(): void
    {
        $this->seedLedger();

        $this->report()
            ->assertSee('45.000,00')
            ->assertSee('8.000,00')
            ->assertSee('260.000,00')
            ->assertSee('297.000,00');
    }

    /**
     * @test
     */
    public function ignores_journals_dated_outside_the_period(): void
    {
        $this->seedLedger();

        $this->report()->assertDontSee('999.000,00');
    }

    /**
     * @test
     *
     * semuaRekening() is what puts an account with no rekeningtahun row and no
     * jurnal at all onto the report - at a zero balance rather than dropped.
     */
    public function lists_an_account_with_no_movement_and_no_opening_balance(): void
    {
        $this->seedLedger();

        $this->report()->assertSee('Rekening Uji Tidak Bergerak');
    }

    /**
     * @test
     *
     * The total row sums each account's own period debet/kredit
     * independently of the per-row figures: 210.000+8.000 debet,
     * 35.000+260.000 kredit.
     */
    public function totals_debet_and_kredit_across_every_account(): void
    {
        $this->seedLedger();

        $this->report()
            ->assertSee('218.000,00')
            ->assertSee('295.000,00');
    }

    /**
     * @test
     */
    public function resetting_the_cache_loads_the_report(): void
    {
        $this->seedLedger();

        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        Livewire::actingAs($petugas)
            ->test(LaporanTrialBalance::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->assertSet('isDeferred', true)
            ->call('resetCache')
            ->assertSet('isDeferred', false)
            ->assertSee('Kas Uji');
    }
}
