<?php

namespace Tests\Feature\Keuangan;

use App\Exceptions\ImportTarifException;
use App\Jobs\Keuangan\ImportTarifLabJob;
use App\Jobs\Keuangan\ImportTarifOperasiJob;
use App\Jobs\Keuangan\ImportTarifRadiologiJob;
use App\Jobs\Keuangan\ImportTarifRalanJob;
use App\Jobs\Keuangan\ImportTarifRanapJob;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\SimpleExcel\SimpleExcelReader;
use Tests\TestCase;

class ImportTarifJobsTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql_sik'];

    private const KELAS = '-, Rawat Jalan, Kelas 1, Kelas 2, Kelas 3, Kelas Utama, Kelas VIP, Kelas VVIP';

    private const KELAS_RANAP = '-, Kelas 1, Kelas 2, Kelas 3, Kelas Utama, Kelas VIP, Kelas VVIP';

    /**
     * job class, template name, code header, table, code column
     */
    private const JOBS = [
        'operasi'   => [ImportTarifOperasiJob::class, 'operasi', 'Kode Paket', 'paket_operasi', 'kode_paket'],
        'lab'       => [ImportTarifLabJob::class, 'lab', 'Kode Periksa', 'jns_perawatan_lab', 'kd_jenis_prw'],
        'radiologi' => [ImportTarifRadiologiJob::class, 'radiologi', 'Kode Periksa', 'jns_perawatan_radiologi', 'kd_jenis_prw'],
        'ralan'     => [ImportTarifRalanJob::class, 'ralan', 'Kode Tindakan', 'jns_perawatan', 'kd_jenis_prw'],
        'ranap'     => [ImportTarifRanapJob::class, 'ranap', 'Kode Tindakan', 'jns_perawatan_inap', 'kd_jenis_prw'],
    ];

    /** @var list<string> */
    private array $notifications = [];

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $notifications = &$this->notifications;

        // sik_test has no Khanza users, so the notifiable is null; NotificationFake can't take that.
        Notification::swap(new class($notifications)
        {
            /** @var list<string> */
            private array $sent;

            public function __construct(array &$sent)
            {
                $this->sent = &$sent;
            }

            public function send($notifiable, $notification): void
            {
                $this->sent[] = (fn () => $this->message)->call($notification);
            }
        });

        // Master rows the Ralan/Ranap templates point at ('-'); sik_test has none.
        DB::connection('mysql_sik')->table('kategori_perawatan')->insertOrIgnore(['kd_kategori' => '-', 'nm_kategori' => '-']);
        DB::connection('mysql_sik')->table('bangsal')->insertOrIgnore(['kd_bangsal' => '-', 'nm_bangsal' => '-', 'status' => '1']);

        $this->file = tempnam(sys_get_temp_dir(), 'tarif').'.xlsx';
    }

    protected function tearDown(): void
    {
        @unlink($this->file);

        parent::tearDown();
    }

    /**
     * Write a file from the job's template. Each entry in $rows is either an
     * array of overrides for the template's sample row (with a unique code),
     * or a string, which fills every cell of the row with that string.
     *
     * @param  list<array<string, mixed>|string>  $rows
     */
    private function import(string $job, array $rows): void
    {
        [$class, $template, $codeHeader] = self::JOBS[$job];

        $sample = SimpleExcelReader::create(public_path("templates/template-import-tarif-{$template}.xlsx"))->getRows()->first();
        $headers = array_keys($sample);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($this->file);
        $writer->addRow(WriterEntityFactory::createRowFromArray($headers));

        foreach ($rows as $i => $row) {
            $values = is_string($row)
                ? array_fill(0, count($headers), $row)
                : array_values(array_merge($sample, [$codeHeader => 'ZTIMP'.$i], $row));

            $writer->addRow(WriterEntityFactory::createRowFromArray($values));
        }

        $writer->close();

        (new $class([
            'fileImport' => new UploadedFile($this->file, 'tarif.xlsx', null, null, true),
            'userId'     => 'test',
        ]))->handle();
    }

    /**
     * @param  list<array<string, mixed>|string>  $rows
     */
    private function assertImportFails(string $job, array $rows, string $expected): void
    {
        try {
            $this->import($job, $rows);
            $this->fail('Import seharusnya gagal');
        } catch (ImportTarifException $e) {
            $this->assertSame($expected, $e->getMessage());
            $this->assertSame($expected, end($this->notifications), 'Pesan notifikasi harus sama dengan pesan exception');
        }

        [, , , $table, $codeColumn] = self::JOBS[$job];
        $this->assertFalse(DB::connection('mysql_sik')->table($table)->where($codeColumn, 'like', 'ZTIMP%')->exists(), 'Transaksi harus rollback');
    }

    /**
     * @return array<string, array{string, array<string, mixed>, string}>
     */
    public static function translatedErrors(): array
    {
        return [
            'operasi: kelas di luar pilihan'   => ['operasi', ['Kelas' => 'VIP'], "Baris 3: Kelas 'VIP' tidak valid. Pilihan: ".self::KELAS.' (kode 1265)'],
            'lab: kategori kosong'             => ['lab', ['Kategori' => ''], 'Baris 3: Kategori wajib diisi. Pilihan: PK, PA, MB (kode 1265)'],
            'radiologi: kelas di luar pilihan' => ['radiologi', ['Kelas' => 'VIP'], "Baris 3: Kelas 'VIP' tidak valid. Pilihan: ".self::KELAS.' (kode 1265)'],
            'ralan: kode terlalu panjang'      => ['ralan', ['Kode Tindakan' => 'ZT-RALAN-000001'.'X'], "Baris 3: Kode Tindakan 'ZT-RALAN-000001X' melebihi 15 karakter (kode 1406)"],
            'ranap: kelas kosong'              => ['ranap', ['Kelas' => ''], 'Baris 3: Kelas wajib diisi. Pilihan: '.self::KELAS_RANAP.' (kode 1265)'],
        ];
    }

    /**
     * @dataProvider translatedErrors
     *
     * @param  array<string, mixed>  $badRow
     */
    public function test_database_error_reaches_notification_translated(string $job, array $badRow, string $expected): void
    {
        $this->assertImportFails($job, [[], $badRow], $expected);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function jobsWithKelasDefault(): array
    {
        return ['operasi' => ['operasi'], 'lab' => ['lab'], 'radiologi' => ['radiologi']];
    }

    /**
     * @dataProvider jobsWithKelasDefault
     */
    public function test_blank_kelas_is_saved_as_strip(string $job): void
    {
        $this->import($job, [['Kelas' => " \u{00A0}"]]);

        [, , , $table, $codeColumn] = self::JOBS[$job];
        $this->assertSame('-', DB::connection('mysql_sik')->table($table)->where($codeColumn, 'ZTIMP0')->value('kelas'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function blankRows(): array
    {
        return [
            'baris kosong'       => [''],
            'baris berisi spasi' => ['   '],
            'baris berisi NBSP'  => ["\u{00A0}\u{202F}"],
        ];
    }

    /**
     * @dataProvider blankRows
     */
    public function test_blank_row_keeps_excel_row_number(string $blank): void
    {
        $this->assertImportFails('operasi', [[], $blank, ['Kelas' => 'VIP']], "Baris 4: Kelas 'VIP' tidak valid. Pilihan: ".self::KELAS.' (kode 1265)');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function jobsWithMasterPreCheck(): array
    {
        return ['ralan' => ['ralan'], 'ranap' => ['ranap']];
    }

    /**
     * @dataProvider jobsWithMasterPreCheck
     */
    public function test_blank_row_keeps_excel_row_number_in_pre_check(string $job): void
    {
        $this->assertImportFails($job, [[], '', ['Jenis Bayar' => 'ZZZ']], "Baris 4: Jenis Bayar 'ZZZ' tidak ditemukan");
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function emptyFiles(): array
    {
        $files = [];

        foreach (array_keys(self::JOBS) as $job) {
            $files["{$job}: hanya header"] = [$job, []];
            $files["{$job}: header + baris kosong"] = [$job, ['', '  ']];
        }

        return $files;
    }

    /**
     * @dataProvider emptyFiles
     *
     * @param  list<string>  $rows
     */
    public function test_file_without_data_rows(string $job, array $rows): void
    {
        $this->assertImportFails($job, $rows, 'Tidak ada data untuk diimport');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function amountHeaders(): array
    {
        return [
            'operasi'   => ['operasi', 'Operator 1'],
            'lab'       => ['lab', 'Jasa Sarana'],
            'radiologi' => ['radiologi', 'Jasa Sarana'],
            'ralan'     => ['ralan', 'Jasa Sarana'],
            'ranap'     => ['ranap', 'Jasa Sarana'],
        ];
    }

    /**
     * @dataProvider amountHeaders
     */
    public function test_text_in_amount_cell_is_rejected(string $job, string $header): void
    {
        $this->assertImportFails($job, [[], [$header => '12abc']], "Baris 3: {$header} '12abc' harus berupa angka (contoh: 1.500 atau 1.500,50)");
    }

    public function test_dot_thousands_text_is_saved_as_thousands(): void
    {
        $this->import('operasi', [['Operator 1' => '1.500']]);

        $this->assertEquals(1500, DB::connection('mysql_sik')->table('paket_operasi')->where('kode_paket', 'ZTIMP0')->value('operator1'));
    }
}
