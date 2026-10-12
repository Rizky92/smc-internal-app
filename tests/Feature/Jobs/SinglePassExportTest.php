<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ExportLabaRugiRekeningJob;
use App\Notifications\Notification as SiapNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

/**
 * Single-Pass Export (see CONTEXT.md and docs/adr/0001): Laba Rugi per Rekening,
 * built in one go on a worker.
 *
 * The job does not reuse the page's query. The screen adds up through
 * Rekening::semuaRekening() and hitungDebetKreditPerPeriode(); the job unions
 * Jurnal::labaRugiRalan(), labaRugiRanap() and labaRugi(). Two implementations
 * of one statement can drift apart without either looking wrong, so the
 * workbook is checked against the same ledger, and the same figures, that
 * LabaRugiRekeningPerPeriodeTest pins on the page.
 */
class SinglePassExportTest extends TestCase
{
    private const USER = '99999901';

    private const PAYLOAD = ['tglAwal' => '2026-03-01', 'tglAkhir' => '2026-03-31', 'kodePenjamin' => ''];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->makeDirectory('excel');
        Notification::fake();
    }

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI-SP%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI-SP%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI.SP%')->delete();

        parent::tearDown();
    }

    /**
     * The ledger from LabaRugiRekeningPerPeriodeTest: income 550.000, expenditure
     * 210.000, profit 340.000, every figure distinct and positive.
     */
    private function seedLedger(): void
    {
        $sik = DB::connection('mysql_sik');

        foreach ([
            ['UJI.SP.4', 'Pendapatan Uji', 'K'],
            ['UJI.SP.7', 'Pendapatan Lain Uji', 'K'],
            ['UJI.SP.5', 'Beban Uji', 'D'],
            ['UJI.SP.8', 'Beban Lain Uji', 'D'],
        ] as [$kode, $nama, $balance]) {
            $sik->table('rekening')->insert(['kd_rek' => $kode, 'nm_rek' => $nama, 'tipe' => 'R', 'balance' => $balance, 'level' => '1']);
        }

        foreach ([
            ['UJI-SP-101', '2026-03-05', 'UJI.SP.4', 0, 500000],
            ['UJI-SP-102', '2026-03-10', 'UJI.SP.4', 50000, 0],
            ['UJI-SP-106', '2026-03-12', 'UJI.SP.7', 0, 100000],
            ['UJI-SP-103', '2026-03-15', 'UJI.SP.5', 200000, 0],
            ['UJI-SP-104', '2026-03-20', 'UJI.SP.5', 0, 20000],
            ['UJI-SP-107', '2026-03-22', 'UJI.SP.8', 30000, 0],
            // Outside the period, and larger than anything inside it.
            ['UJI-SP-105', '2026-02-15', 'UJI.SP.4', 0, 999000],
        ] as [$no, $tanggal, $rek, $debet, $kredit]) {
            $sik->table('jurnal')->insert([
                'no_jurnal'  => $no, 'no_bukti' => 'BUKTI-'.$no, 'tgl_jurnal' => $tanggal,
                'jam_jurnal' => '09:00:00', 'jenis' => 'U', 'keterangan' => 'Jurnal '.$no,
            ]);
            $sik->table('detailjurnal')->insert(['no_jurnal' => $no, 'kd_rek' => $rek, 'debet' => $debet, 'kredit' => $kredit]);
        }
    }

    /**
     * @return array<string, list<string>> each row's cells, keyed by its label
     */
    private function barisBerlabel(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);

        $baris = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = array_map(fn ($v) => is_float($v) || is_int($v) ? (string) round($v, 2) : (string) $v, $row->toArray());
                $label = $cells[1] ?? '';
                $key = $label === '-' || $label === '' ? ($cells[2] ?? '') : $label;

                if ($key !== '') {
                    $baris[$key] = $cells;
                }
            }

            break;
        }

        $reader->close();

        return $baris;
    }

    /**
     * @test
     */
    public function the_job_runs_on_the_exports_queue(): void
    {
        $this->assertSame('exports', (new ExportLabaRugiRekeningJob(self::USER, self::PAYLOAD))->queue);
    }

    /**
     * @test
     *
     * Columns are Unit, Dokter, Kode Akun, Nama Akun, Jenis, Debet, Kredit,
     * Total. Per-account rows are checked where the sign convention is applied,
     * and the three summary rows because they are summed separately.
     */
    public function the_workbook_reports_the_same_figures_as_the_page(): void
    {
        $user = $this->petugasWithPermissions([], self::USER);
        $this->seedLedger();

        (new ExportLabaRugiRekeningJob(self::USER, self::PAYLOAD))->handle();

        $file = collect(Storage::disk('public')->files('excel'))->first(fn ($f) => str_ends_with($f, '.xlsx'));
        $this->assertNotNull($file, 'Job tidak menyimpan berkas .xlsx.');

        $baris = $this->barisBerlabel(Storage::disk('public')->path($file));

        $this->assertSame('450000', $baris['UJI.SP.4'][7]);
        $this->assertSame('180000', $baris['UJI.SP.5'][7]);
        $this->assertSame('550000', $baris['TOTAL PENDAPATAN'][7]);
        $this->assertSame('210000', $baris['TOTAL BEBAN & BIAYA'][7]);
        $this->assertSame('340000', $baris['PENDAPATAN BERSIH'][7]);

        Notification::assertSentTo($user, SiapNotification::class, function (SiapNotification $n) use ($user) {
            $data = $n->toArray($user);

            return $data['status'] === 'success' && Storage::disk('public')->exists($data['file']);
        });
    }

    /**
     * @test
     */
    public function a_failed_export_tells_the_user(): void
    {
        $user = $this->petugasWithPermissions([], self::USER);

        (new ExportLabaRugiRekeningJob(self::USER, self::PAYLOAD))->failed(new \RuntimeException('uji'));

        Notification::assertSentTo($user, SiapNotification::class, fn (SiapNotification $n) => $n->toArray($user)['status'] === 'error');
    }

    /**
     * A SIAP user's id is Khanza's NIK, a string that is not always a number.
     * ExcelExportJob used to declare `int $userId`, so for an id like "A0419"
     * the job could not even be constructed and the export button failed on the
     * spot — 141 of the 801 login accounts on the dev Khanza database.
     *
     * Constructing it is not enough to show the fix: the id is used again at the
     * end, to find who to notify, so the whole job runs for such a user and the
     * notification has to reach them.
     *
     * @test
     */
    public function a_user_with_a_non_numeric_nik_gets_the_export_and_the_notification(): void
    {
        $nik = '9999990A';
        $user = $this->petugasWithPermissions([], $nik);
        $this->seedLedger();

        $job = new ExportLabaRugiRekeningJob($nik, self::PAYLOAD);
        $job->handle();

        $this->assertNotNull(
            collect(Storage::disk('public')->files('excel'))->first(fn ($f) => str_ends_with($f, '.xlsx')),
            'Job tidak menyimpan berkas .xlsx.'
        );

        Notification::assertSentTo($user, SiapNotification::class, fn (SiapNotification $n) => $n->toArray($user)['status'] === 'success');
    }

    /**
     * And a numeric NIK is kept exactly as Khanza has it, not turned into a
     * number: a leading zero would otherwise be lost and the user not found.
     *
     * @test
     */
    public function the_nik_is_kept_verbatim(): void
    {
        $job = new ExportLabaRugiRekeningJob('0012345', self::PAYLOAD);

        $this->assertSame('0012345', (new \ReflectionProperty($job, 'userId'))->getValue($job));
    }
}
