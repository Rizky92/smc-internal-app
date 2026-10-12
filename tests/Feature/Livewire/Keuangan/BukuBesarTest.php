<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Jobs\PrepareExport;
use App\Jobs\WriteExcel;
use App\Livewire\Pages\Keuangan\BukuBesar;
use App\Models\ExportSession;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use ReflectionProperty;
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

        ExportSession::query()->where('export_name', 'buku-besar')->where('id_user', 'like', '9999990%')->delete();

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

    /**
     * @test
     *
     * exportToBackground() used to flash this warning with $this->emit(),
     * removed in Livewire 3 - calling it would have been a fatal error the
     * moment a user already had an export running.
     */
    public function refuses_a_second_background_export_while_one_is_running(): void
    {
        ExportSession::query()->create([
            'session_id'  => 'UJI-SESSION',
            'id_user'     => '99999901',
            'export_name' => 'buku-besar',
            'status'      => 'processing',
        ]);

        $petugas = $this->petugasWithPermissions([self::URI_PERMISSION], '99999901');

        Livewire::actingAs($petugas)
            ->test(BukuBesar::class)
            ->call('exportToBackground')
            ->assertDispatched('flash.error');
    }

    private function exporter()
    {
        return Livewire::actingAs($this->petugasWithPermissions([self::URI_PERMISSION], '99999901'))
            ->test(BukuBesar::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->set('kodeRekening', 'UJI.1');
    }

    private function prop(object $job, string $name)
    {
        return (new ReflectionProperty($job, $name))->getValue($job);
    }

    /**
     * @test
     *
     * Requesting a Sharded Export opens an Export Session and queues exactly one
     * batch on the exports queue, whose only starting job is PrepareExport for
     * that session, carrying the filters on screen at the moment of the click.
     */
    public function a_background_export_opens_a_session_and_queues_the_preparation(): void
    {
        Bus::fake();
        Notification::fake();

        $this->exporter()
            ->call('exportWithOption', 2)
            ->assertDispatched('flash.info')
            ->assertNotDispatched('beginExcelExport');

        $sesi = ExportSession::query()->where('id_user', '99999901')->where('export_name', 'buku-besar')->sole();

        $this->assertSame('pending', $sesi->status);

        Bus::assertBatched(function (PendingBatch $batch) use ($sesi) {
            $prepare = $batch->jobs->first();

            return $batch->queue() === 'exports'
                && $batch->jobs->count() === 1
                && $prepare instanceof PrepareExport
                && $this->prop($prepare, 'exportSessionId') === $sesi->session_id
                && $this->prop($prepare, 'userId') === '99999901'
                && $this->prop($prepare, 'tglAwal') === self::AWAL
                && $this->prop($prepare, 'tglAkhir') === self::AKHIR
                && $this->prop($prepare, 'kodeRekening') === 'UJI.1';
        });
    }

    /**
     * @test
     *
     * The batch's then-callback is what starts the last step. If it pointed at
     * another session, or another queue, the shards would be written and the
     * workbook never built — and the session would stay "processing" forever,
     * locking the user out of exporting this report again.
     */
    public function once_every_shard_is_written_the_workbook_is_queued_for_the_same_session(): void
    {
        Bus::fake();
        Notification::fake();

        $this->exporter()->call('exportToBackground');

        $sesi = ExportSession::query()->where('id_user', '99999901')->where('export_name', 'buku-besar')->sole();

        Bus::assertBatched(function (PendingBatch $batch) {
            foreach ($batch->thenCallbacks() as $then) {
                $then();
            }

            return true;
        });

        Bus::assertDispatched(WriteExcel::class, fn (WriteExcel $job) => $job->queue === 'exports'
            && $this->prop($job, 'exportSessionId') === $sesi->session_id
            && $this->prop($job, 'userId') === '99999901'
            && $this->prop($job, 'exportName') === 'buku-besar');
    }

    /**
     * @test
     *
     * "Pending" locks the report as firmly as "processing": the batch may simply
     * not have been picked up yet.
     */
    public function refuses_a_second_background_export_while_the_first_is_still_queued(): void
    {
        Bus::fake();

        ExportSession::query()->create([
            'session_id' => 'UJI-SESSION', 'id_user' => '99999901', 'export_name' => 'buku-besar', 'status' => 'pending',
        ]);

        $this->exporter()
            ->call('exportToBackground')
            ->assertDispatched('flash.error');

        Bus::assertNothingBatched();
        $this->assertSame(1, ExportSession::query()->where('id_user', '99999901')->count());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function statusSelesai(): array
    {
        return ['selesai' => ['completed'], 'gagal' => ['failed']];
    }

    /**
     * @test
     *
     * @dataProvider statusSelesai
     *
     * The lock lifts once the earlier export has finished either way. A failed
     * one in particular must not keep the user from trying again.
     */
    public function a_finished_export_does_not_block_the_next_one(string $status): void
    {
        Bus::fake();
        Notification::fake();

        ExportSession::query()->create([
            'session_id' => 'UJI-SESSION', 'id_user' => '99999901', 'export_name' => 'buku-besar', 'status' => $status,
        ]);

        $this->exporter()
            ->call('exportToBackground')
            ->assertNotDispatched('flash.error');

        Bus::assertBatchCount(1);
    }

    /**
     * @test
     *
     * The lock is per user. One member of staff exporting the ledger must not
     * stop another from doing the same.
     */
    public function another_users_running_export_does_not_block_this_one(): void
    {
        Bus::fake();
        Notification::fake();

        ExportSession::query()->create([
            'session_id' => 'UJI-SESSION', 'id_user' => '99999902', 'export_name' => 'buku-besar', 'status' => 'processing',
        ]);

        $this->exporter()
            ->call('exportToBackground')
            ->assertNotDispatched('flash.error');

        Bus::assertBatchCount(1);
    }

    /**
     * @test
     *
     * The other option is the Synchronous Export, which must not touch the queue
     * or open a session.
     */
    public function the_synchronous_option_downloads_in_place_without_queueing(): void
    {
        Bus::fake();

        $this->exporter()
            ->call('exportWithOption', 1)
            ->assertDispatched('beginExcelExport');

        Bus::assertNothingBatched();
        $this->assertSame(0, ExportSession::query()->where('id_user', '99999901')->count());
    }
}
