<?php

namespace Tests\Feature\Jobs;

use App\Jobs\BukuBesarExport;
use App\Models\ExportSession;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class BukuBesarExportTest extends TestCase
{
    private FilesystemAdapter $disk;

    private string $userId = 'test-buku-besar-export';

    private array $headers = ['Tgl', 'Jam', 'No. Jurnal', 'No. Bukti', 'Keterangan Jurnal', 'Kode', 'Rekening', 'Debet', 'Kredit'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = Storage::fake('public');

        $this->hapusDataUji();

        $sik = DB::connection('mysql_sik');

        $sik->table('rekening')->insert([
            ['kd_rek' => 'TST-A', 'nm_rek' => 'Rekening A'],
            ['kd_rek' => 'TST-B', 'nm_rek' => 'Rekening B'],
        ]);

        /*
         * Sengaja diinsert tidak berurutan supaya urutan hasil benar-benar
         * berasal dari ORDER BY, bukan dari urutan insert.
         */
        $sik->table('jurnal')->insert([
            ['no_jurnal' => 'TSTJ03', 'no_bukti' => 'B3', 'tgl_jurnal' => '2099-01-02', 'jam_jurnal' => '08:00:00', 'keterangan' => 'Jurnal 3'],
            ['no_jurnal' => 'TSTJ01', 'no_bukti' => 'B1', 'tgl_jurnal' => '2099-01-01', 'jam_jurnal' => '09:00:00', 'keterangan' => 'Jurnal 1'],
            ['no_jurnal' => 'TSTJ02', 'no_bukti' => 'B2', 'tgl_jurnal' => '2099-01-01', 'jam_jurnal' => '10:00:00', 'keterangan' => 'Jurnal 2'],
            ['no_jurnal' => 'TSTJ99', 'no_bukti' => 'B9', 'tgl_jurnal' => '2099-02-01', 'jam_jurnal' => '08:00:00', 'keterangan' => 'Di luar periode'],
        ]);

        $sik->table('detailjurnal')->insert([
            ['no_jurnal' => 'TSTJ03', 'kd_rek' => 'TST-A', 'debet' => 5, 'kredit' => 0],
            ['no_jurnal' => 'TSTJ01', 'kd_rek' => 'TST-B', 'debet' => 0, 'kredit' => 100],
            ['no_jurnal' => 'TSTJ01', 'kd_rek' => 'TST-A', 'debet' => 100, 'kredit' => 0],
            // Dua baris dengan debet sama, dibedakan kredit lalu kd_rek.
            ['no_jurnal' => 'TSTJ02', 'kd_rek' => 'TST-B', 'debet' => 50, 'kredit' => 0],
            ['no_jurnal' => 'TSTJ02', 'kd_rek' => 'TST-A', 'debet' => 50, 'kredit' => 0],
            ['no_jurnal' => 'TSTJ02', 'kd_rek' => 'TST-B', 'debet' => 50, 'kredit' => 20],
            ['no_jurnal' => 'TSTJ99', 'kd_rek' => 'TST-A', 'debet' => 1, 'kredit' => 0],
        ]);
    }

    protected function tearDown(): void
    {
        $this->hapusDataUji();

        ExportSession::query()->where('id_user', $this->userId)->delete();

        parent::tearDown();
    }

    public function test_menghasilkan_xlsx_berurutan_sesuai_khanza_dengan_header(): void
    {
        $session = $this->buatSession();

        BukuBesarExport::dispatchSync($this->params($session));

        $sheets = $this->bacaXlsx($session);

        $this->assertCount(1, $sheets);
        $this->assertSame($this->headers, $sheets[0][0]);

        $this->assertSame([
            ['TSTJ01', 'TST-A', 100, 0],
            ['TSTJ01', 'TST-B', 0, 100],
            ['TSTJ02', 'TST-B', 50, 20],
            ['TSTJ02', 'TST-A', 50, 0],
            ['TSTJ02', 'TST-B', 50, 0],
            ['TSTJ03', 'TST-A', 5, 0],
        ], $this->ringkas(array_slice($sheets[0], 1)));

        $this->assertSame(
            ['2099-01-01', '09:00:00', 'TSTJ01', 'B1', 'Jurnal 1', 'TST-A', 'Rekening A', 100, 0],
            $this->normalkan($sheets[0][1])
        );
    }

    public function test_sheet_baru_dibuat_saat_mencapai_batas_baris_dan_header_diulang(): void
    {
        $session = $this->buatSession();

        // 3 baris per sheet = 1 header + 2 data, sehingga 6 baris data menjadi 3 sheet.
        BukuBesarExport::dispatchSync($this->params($session, ['maxRowsPerSheet' => 3]));

        $sheets = $this->bacaXlsx($session);

        $this->assertCount(3, $sheets);

        foreach ($sheets as $sheet) {
            $this->assertCount(3, $sheet);
            $this->assertSame($this->headers, $sheet[0]);
        }

        $dataLintasSheet = collect($sheets)->flatMap(fn (array $sheet) => array_slice($sheet, 1))->all();

        $this->assertSame([
            ['TSTJ01', 'TST-A', 100, 0],
            ['TSTJ01', 'TST-B', 0, 100],
            ['TSTJ02', 'TST-B', 50, 20],
            ['TSTJ02', 'TST-A', 50, 0],
            ['TSTJ02', 'TST-B', 50, 0],
            ['TSTJ03', 'TST-A', 5, 0],
        ], $this->ringkas($dataLintasSheet));
    }

    public function test_filter_kode_rekening_dan_periode(): void
    {
        $session = $this->buatSession();

        BukuBesarExport::dispatchSync($this->params($session, [
            'tglAwal'      => '2099-01-01',
            'tglAkhir'     => '2099-01-01',
            'kodeRekening' => 'TST-A',
        ]));

        $sheets = $this->bacaXlsx($session);

        $this->assertSame([
            ['TSTJ01', 'TST-A', 100, 0],
            ['TSTJ02', 'TST-A', 50, 0],
        ], $this->ringkas(array_slice($sheets[0], 1)));
    }

    public function test_session_selesai_dan_data_antara_tidak_ditulis(): void
    {
        $session = $this->buatSession();

        BukuBesarExport::dispatchSync($this->params($session));

        $this->assertSame('completed', $session->fresh()->status);

        $files = $this->disk->allFiles("exports/{$this->userId}/{$session->session_id}");

        $this->assertCount(1, $files);
        $this->assertStringEndsWith("_{$this->userId}_{$session->session_id}_buku-besar.xlsx", $files[0]);
    }

    public function test_export_gagal_tidak_meninggalkan_file_setengah_jadi(): void
    {
        $session = $this->buatSession();

        config(['database.connections.mysql_sik_export.database' => 'database_yang_tidak_ada']);
        DB::purge('mysql_sik_export');

        try {
            BukuBesarExport::dispatchSync($this->params($session));

            $this->fail('Export seharusnya gagal.');
        } catch (\Throwable $e) {
            // Diharapkan: koneksi ke database gagal.
        }

        $this->assertSame([], $this->disk->allFiles("exports/{$this->userId}/{$session->session_id}"));
        $this->assertSame('failed', $session->fresh()->status);
    }

    public function test_failed_membersihkan_file_saat_worker_dihentikan_karena_timeout(): void
    {
        /*
         * Saat timeout, worker memanggil failed() lalu mematikan proses,
         * sehingga catch/finally di handle() tidak pernah berjalan. failed()
         * harus bisa membersihkan sendiri hanya dari parameter job.
         */
        $session = $this->buatSession();

        $tempDir = storage_path('framework/testing/export-temp');
        config(['export.temp_dir' => $tempDir]);

        $this->disk->put("exports/{$this->userId}/{$session->session_id}/setengah-jadi.xlsx", 'partial');
        mkdir($tempDir.'/export-'.$session->session_id, 0777, true);
        file_put_contents($tempDir.'/export-'.$session->session_id.'/sheet1.xml', 'partial');

        (new BukuBesarExport($this->params($session)))->failed(new \RuntimeException('timeout'));

        $this->assertSame([], $this->disk->allFiles("exports/{$this->userId}/{$session->session_id}"));
        $this->assertDirectoryDoesNotExist($tempDir.'/export-'.$session->session_id);
        $this->assertSame('failed', $session->fresh()->status);

        File::deleteDirectory($tempDir);
    }

    private function buatSession(): ExportSession
    {
        return ExportSession::query()->create([
            'session_id'  => Str::uuid()->toString(),
            'id_user'     => $this->userId,
            'export_name' => 'buku-besar',
            'status'      => 'pending',
        ]);
    }

    private function params(ExportSession $session, array $override = []): array
    {
        return array_merge([
            'exportSessionId' => $session->session_id,
            'exportName'      => 'buku-besar',
            'userId'          => $this->userId,
            'tglAwal'         => '2099-01-01',
            'tglAkhir'        => '2099-01-31',
            'kodeRekening'    => '',
            'columnHeaders'   => $this->headers,
        ], $override);
    }

    /**
     * @return array<int, array<int, array>> baris per sheet
     */
    private function bacaXlsx(ExportSession $session): array
    {
        $files = $this->disk->allFiles("exports/{$this->userId}/{$session->session_id}");

        $this->assertCount(1, $files);

        $reader = new Reader;
        $reader->open($this->disk->path($files[0]));

        $sheets = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $rows = [];

            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }

            $sheets[] = $rows;
        }

        $reader->close();

        return $sheets;
    }

    /**
     * Ambil no_jurnal, kd_rek, debet, kredit untuk membandingkan urutan.
     */
    private function ringkas(array $rows): array
    {
        return array_map(function (array $row) {
            $row = $this->normalkan($row);

            return [$row[2], $row[5], $row[7], $row[8]];
        }, $rows);
    }

    private function normalkan(array $row): array
    {
        return array_map(fn ($value) => is_float($value) && floor($value) === $value ? (int) $value : $value, $row);
    }

    private function hapusDataUji(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'TSTJ%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'TSTJ%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'TST-%')->delete();
    }
}
