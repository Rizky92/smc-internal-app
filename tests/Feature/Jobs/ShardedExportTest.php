<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ExportCsv;
use App\Jobs\PrepareExport;
use App\Jobs\WriteExcel;
use App\Models\Aplikasi\User;
use App\Models\Export;
use App\Models\ExportSession;
use App\Notifications\Notification as SiapNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Sharded Export (see CONTEXT.md and docs/adr/0001), taken apart job by job.
 *
 * The pipeline is PrepareExport -> one ExportCsv per shard -> WriteExcel, tied
 * together by an Export Session and a staging table (`exports`) whose `id` is
 * the row order. Each job is exercised on its own here, the way the queue runs
 * them, rather than through Bus::batch(): the batch is plumbing, and every
 * property the user relies on — every row present, in order, in one workbook,
 * with the session and notification telling the truth — is decided inside one
 * of these three handle() methods.
 */
class ShardedExportTest extends TestCase
{
    private const USER = '99999901';

    private const EXPORT = 'buku-besar';

    private const SESI = '00000000-0000-0000-0000-00000000a001';

    private const SESI_LAIN = '00000000-0000-0000-0000-00000000a002';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Notification::fake();
    }

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->table('exports')->where('id_user', 'like', '9999990%')->delete();
        $smc->table('export_sessions')->where('id_user', 'like', '9999990%')->delete();

        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI-EXP%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI-EXP%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI.EXP%')->delete();

        parent::tearDown();
    }

    private function user(string $nik = self::USER): User
    {
        return $this->petugasWithPermissions([], $nik);
    }

    private function sesi(string $sessionId = self::SESI, string $status = 'processing', string $userId = self::USER): ExportSession
    {
        return ExportSession::query()->create([
            'session_id'  => $sessionId,
            'id_user'     => $userId,
            'export_name' => self::EXPORT,
            'status'      => $status,
        ]);
    }

    /**
     * Stage $jumlah rows the way InsertToTemporary() leaves them: `id` 1..n
     * carrying the row order, column1 naming the row so order is checkable.
     */
    private function staging(int $jumlah, string $sessionId = self::SESI, string $userId = self::USER): void
    {
        $rows = [];

        for ($id = 1; $id <= $jumlah; $id++) {
            $rows[] = [
                'id'                => $id,
                'export_session_id' => $sessionId,
                'export_name'       => self::EXPORT,
                'id_user'           => $userId,
                'column1'           => sprintf('baris-%03d', $id),
                'column2'           => '2026-03-01',
            ];
        }

        DB::connection('mysql_smc')->table('exports')->insert($rows);
    }

    private function prepare(int $chunkSize = 1000): PrepareExport
    {
        return new PrepareExport([
            'exportSessionId' => self::SESI,
            'exportName'      => self::EXPORT,
            'userId'          => self::USER,
            'tglAwal'         => '2026-03-01',
            'tglAkhir'        => '2026-03-31',
            'kodeRekening'    => '',
            'columnHeaders'   => ['Tanggal', 'Jam', 'No. Jurnal'],
            'chunkSize'       => $chunkSize,
        ]);
    }

    private function prop(object $job, string $name)
    {
        return (new ReflectionProperty($job, $name))->getValue($job);
    }

    private function shardDir(string $sessionId = self::SESI): string
    {
        return 'exports/'.self::USER.'/'.$sessionId;
    }

    /**
     * @return list<list<string>>
     */
    private function bacaXlsx(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map('strval', $row->toArray());
            }

            break;
        }

        $reader->close();

        return $rows;
    }

    /**
     * @test
     *
     * A shard is one CSV holding exactly the ids it was handed, in `id` order
     * whatever order they arrived in, and only from its own session: another
     * export of the same report by the same user sits in the same table.
     */
    public function a_shard_holds_its_own_rows_in_id_order(): void
    {
        $this->staging(5);
        $this->staging(5, self::SESI_LAIN);

        (new ExportCsv([
            'exportSessionId' => self::SESI,
            'exportName'      => self::EXPORT,
            'userId'          => self::USER,
            'records'         => [4, 2, 3],
            'page'            => 7,
        ]))->handle();

        $isi = Storage::disk('public')->get($this->shardDir().'/0000000000000007.csv');
        $baris = array_values(array_filter(explode("\n", $isi)));

        $this->assertSame(['baris-002', 'baris-003', 'baris-004'], array_map(fn ($b) => str_getcsv($b)[0], $baris));
    }

    /**
     * @test
     *
     * PrepareCsv() writes the header row once, then cuts the staged ids into
     * shards of chunkSize. The ids are read in outer chunks ten times that size,
     * and the page counter has to carry across those outer chunks: restarting
     * it would give two shards the same file name, and the second would silently
     * overwrite the first.
     */
    public function preparing_shards_numbers_them_continuously_across_outer_chunks(): void
    {
        $this->staging(25);

        [$job, $batch] = $this->prepare(2)->withFakeBatch();

        $job->PrepareCsv();

        $this->assertSame("Tanggal,Jam,\"No. Jurnal\"\n", Storage::disk('public')->get($this->shardDir().'/headers.csv'));

        $shards = collect($batch->added)->filter(fn ($j) => $j instanceof ExportCsv)->values();

        // 25 rows in shards of 2: thirteen shards, the last holding one row.
        $this->assertCount(13, $shards);
        $this->assertSame(range(1, 13), $shards->map(fn ($j) => $this->prop($j, 'page'))->all());
        $this->assertSame(range(1, 25), $shards->flatMap(fn ($j) => $this->prop($j, 'records'))->all());
    }

    /**
     * @test
     *
     * WriteExcel stitches headers.csv and every shard into one workbook. Shards
     * 2 and 10 are chosen on purpose: sorted as plain strings "10" comes before
     * "2", which is why shard names are zero-padded and the file list is sorted.
     */
    public function the_workbook_holds_every_shard_in_page_order_under_the_header(): void
    {
        $this->user();
        $this->sesi();
        $this->staging(4);

        $disk = Storage::disk('public');
        $disk->put($this->shardDir().'/headers.csv', "Kolom\n");
        $disk->put($this->shardDir().'/0000000000000010.csv', "baris-003\nbaris-004\n");
        $disk->put($this->shardDir().'/0000000000000002.csv', "baris-001\nbaris-002\n");

        (new WriteExcel(['userId' => self::USER, 'exportSessionId' => self::SESI, 'exportName' => self::EXPORT]))->handle();

        $xlsx = collect($disk->files($this->shardDir()))->first(fn ($f) => str_ends_with($f, '.xlsx'));

        $this->assertNotNull($xlsx, 'WriteExcel tidak menghasilkan berkas .xlsx.');
        $this->assertSame(
            [['Kolom'], ['baris-001'], ['baris-002'], ['baris-003'], ['baris-004']],
            $this->bacaXlsx($disk->path($xlsx))
        );
    }

    /**
     * @test
     *
     * Finishing is three promises kept together: the staging rows are dropped
     * (only this session's), the session says completed, and the user is told
     * where the file is — with a path that actually exists.
     */
    public function finishing_cleans_up_marks_the_session_and_notifies_with_a_real_path(): void
    {
        $user = $this->user();
        $this->sesi();
        $this->sesi(self::SESI_LAIN);
        $this->staging(2);
        $this->staging(2, self::SESI_LAIN);

        Storage::disk('public')->put($this->shardDir().'/headers.csv', "Kolom\n");
        Storage::disk('public')->put($this->shardDir().'/0000000000000001.csv', "baris-001\n");

        (new WriteExcel(['userId' => self::USER, 'exportSessionId' => self::SESI, 'exportName' => self::EXPORT]))->handle();

        $this->assertSame(0, Export::query()->where('export_session_id', self::SESI)->count());
        $this->assertSame(2, Export::query()->where('export_session_id', self::SESI_LAIN)->count());

        $this->assertSame('completed', ExportSession::query()->where('session_id', self::SESI)->value('status'));
        $this->assertSame('processing', ExportSession::query()->where('session_id', self::SESI_LAIN)->value('status'));

        Notification::assertSentTo($user, SiapNotification::class, function (SiapNotification $n) use ($user) {
            $data = $n->toArray($user);

            return $data['status'] === 'success'
                && $data['file'] !== null
                && Storage::disk('public')->exists($data['file']);
        });
    }

    /**
     * @test
     *
     * A failure anywhere in the chain has to release the Export Session, or the
     * report stays locked: exportToBackground() refuses a new request while a
     * session is pending or processing.
     */
    public function a_failed_preparation_releases_the_session_and_tells_the_user(): void
    {
        $user = $this->user();
        $this->sesi();
        $this->sesi(self::SESI_LAIN);

        $this->prepare()->failed(new \RuntimeException('uji'));

        $this->assertSame('failed', ExportSession::query()->where('session_id', self::SESI)->value('status'));
        $this->assertSame('processing', ExportSession::query()->where('session_id', self::SESI_LAIN)->value('status'));

        Notification::assertSentTo($user, SiapNotification::class, fn (SiapNotification $n) => $n->toArray($user)['status'] === 'error');
    }

    /**
     * @test
     */
    public function a_failed_workbook_releases_the_session_and_tells_the_user(): void
    {
        $user = $this->user();
        $this->sesi();

        (new WriteExcel(['userId' => self::USER, 'exportSessionId' => self::SESI, 'exportName' => self::EXPORT]))
            ->failed(new \RuntimeException('uji'));

        $this->assertSame('failed', ExportSession::query()->where('session_id', self::SESI)->value('status'));

        Notification::assertSentTo($user, SiapNotification::class, fn (SiapNotification $n) => $n->toArray($user)['status'] === 'error');
    }

    /**
     * @test
     *
     * Staging starts by deleting whatever this user left for this report — a
     * crashed earlier run never reaches WriteExcel's cleanup. It must not reach
     * past that: another user's export of the same report is still running.
     */
    public function staging_clears_this_users_leftovers_and_nobody_elses(): void
    {
        $this->staging(3, self::SESI_LAIN);
        $this->staging(3, '00000000-0000-0000-0000-00000000a003', '99999902');

        $this->prepare()->InsertToTemporary();

        $this->assertSame(0, Export::query()->where('export_session_id', self::SESI_LAIN)->count());
        $this->assertSame(3, Export::query()->where('id_user', '99999902')->count());
    }

    /**
     * DEFECT, recorded rather than asserted as correct.
     *
     * InsertToTemporary() reads `jurnal` through the model, which qualifies it
     * with the configured Khanza database, but joins detailjurnal and rekening
     * as the literal `sik.detailjurnal` and `sik.rekening`. Two databases end up
     * in one query: under this suite the headers come from sik_test and the
     * lines from the developer's own `sik`, so nothing seeded here is ever
     * staged. On any server whose Khanza schema is not named `sik`, the same
     * export either fails outright or quietly reads the wrong database.
     *
     * Qualifying the joins from the connection, as Jurnal's other scopes already
     * do with DB::connection('mysql_sik')->getDatabaseName(), fixes it. Flip this
     * test to assert the two seeded lines, in order, when that lands — the
     * ordering rules in InsertToTemporary() are untestable until then.
     *
     * @test
     */
    public function staging_currently_ignores_the_configured_khanza_database(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('rekening')->insert(['kd_rek' => 'UJI.EXP.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'N', 'balance' => 'D', 'level' => '1']);
        $sik->table('jurnal')->insert([
            'no_jurnal'  => 'UJI-EXP-001', 'no_bukti' => 'B-1', 'tgl_jurnal' => '2026-03-05',
            'jam_jurnal' => '09:00:00', 'jenis' => 'U', 'keterangan' => 'Uji ekspor',
        ]);
        $sik->table('detailjurnal')->insert([
            ['no_jurnal' => 'UJI-EXP-001', 'kd_rek' => 'UJI.EXP.1', 'debet' => 1000, 'kredit' => 0],
            ['no_jurnal' => 'UJI-EXP-001', 'kd_rek' => 'UJI.EXP.1', 'debet' => 0, 'kredit' => 1000],
        ]);

        try {
            $this->prepare()->InsertToTemporary();
        } catch (QueryException $e) {
            // A machine without a `sik` schema fails here instead: the same defect.
        }

        $this->assertSame(
            0,
            Export::query()->where('export_session_id', self::SESI)->count(),
            'Baris jurnal dari database Khanza yang dikonfigurasi ternyata ter-staging; balik test ini.'
        );
    }
}
