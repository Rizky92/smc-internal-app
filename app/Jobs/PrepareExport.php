<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesExportSession;
use App\Models\Aplikasi\User;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Notifications\Notification;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class PrepareExport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use HandlesExportSession;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    public $timeout = 3600;

    private string $tglAwal;

    private string $tglAkhir;

    private string $kodeRekening;

    private array $columnHeaders;

    private int $chunkSize = 1000;

    /**
     * @param  array{
     *      exportSessionId: string,
     *      exportName: string,
     *      userId: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      kodeRekening: string,
     *      columnHeaders: array,
     *      chunkSize: int
     * }  $params
     */
    public function __construct(array $params)
    {
        $this->exportSessionId = $params['exportSessionId'];
        $this->exportName = $params['exportName'];
        $this->userId = $params['userId'];
        $this->tglAwal = $params['tglAwal'];
        $this->tglAkhir = $params['tglAkhir'];
        $this->kodeRekening = $params['kodeRekening'] ?? '';
        $this->columnHeaders = $params['columnHeaders'];
        $this->chunkSize = $params['chunkSize'] ?? 1000;
    }

    public function handle(): void
    {
        $this->updateSessionStatus('processing');
        $this->InsertToTemporary();
        $this->PrepareCsv();
    }

    public function PrepareCsv(): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(',');
        $csv->insertOne($this->columnHeaders);

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/headers.csv";
        Storage::disk('public')->put($filePath, $csv->toString());

        $exportCsvJob = $this->getExportCsvJob();

        $page = 1;

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$page): void {
            $jobs = [];

            foreach (array_chunk($records, $this->chunkSize) as $recordChunk) {
                $jobs[] = new $exportCsvJob([
                    'exportSessionId' => $this->exportSessionId,
                    'exportName'      => $this->exportName,
                    'userId'          => $this->userId,
                    'records'         => $recordChunk,
                    'page'            => $page,
                ]);

                $page++;
            }

            $this->batch()->add($jobs);
        };

        $chunkKeySize = $this->chunkSize * 10;

        $baseQuery = DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('export_name', $this->exportName)
            ->where('id_user', $this->userId);

        $baseQuery
            ->select(['id'])
            ->chunkById(
                $chunkKeySize,
                fn (Collection $records) => $dispatchRecords(
                    Arr::pluck($records->all(), 'id')
                ), 'id');
    }

    public function InsertToTemporary(): void
    {
        /*
         * INSERT ... SELECT ini dijalankan lewat koneksi mysql_smc_export yang
         * memakai isolasi READ COMMITTED. Di bawah REPEATABLE READ, InnoDB
         * memasang shared next-key lock pada seluruh baris sik.jurnal,
         * sik.detailjurnal, dan sik.rekening yang dibaca, dan menahannya sampai
         * statement selesai. Karena Khanza terus menulis ke tabel jurnal yang
         * sama, siklus tunggu-menunggu itu berakhir sebagai deadlock (1213) di
         * tengah proses export. Dengan READ COMMITTED, pembacaan tabel sumber
         * menjadi consistent read tanpa lock sama sekali.
         *
         * Nomor urut `id` dibuat lewat row_number() supaya kolomnya tidak perlu
         * AUTO_INCREMENT, sehingga statement ini tidak lagi memegang AUTO-INC
         * lock setingkat tabel selama berjalan.
         */
        $connection = DB::connection('mysql_smc_export');

        $urutan = <<<'SQL'
            row_number() over (
                order by
                    jurnal.tgl_jurnal asc,
                    jurnal.jam_jurnal asc,
                    jurnal.no_jurnal asc
            )
            SQL;

        $query = Jurnal::on('mysql_sik')
            ->select([
                DB::raw("'$this->exportSessionId' as export_session_id"),
                DB::raw("'$this->exportName' as export_name"),
                DB::raw("'$this->userId' as id_user"),
                DB::raw("$urutan as id"),
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'rekening.nm_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit',
            ])
            ->join('sik.detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('sik.rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($this->kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $this->kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$this->tglAwal, $this->tglAkhir]);

        /*
         * Urutan baris hasil export ditentukan oleh `id`, dan `id` sudah
         * diurutkan oleh window function di atas. ORDER BY pada query luar
         * hanya akan menambah filesort atas seluruh hasil tanpa mengubah apa
         * pun, jadi sengaja tidak dipasang.
         *
         * transaction() dengan 3 percobaan dipakai agar deadlock atau lock wait
         * dari sumber lain otomatis diulang. DELETE ikut masuk ke dalam
         * transaksi supaya percobaan ulang selalu berangkat dari kondisi bersih.
         */
        $connection->transaction(function () use ($connection, $query): void {
            $connection->table('exports')
                ->where('id_user', $this->userId)
                ->where('export_name', $this->exportName)
                ->delete();

            $connection->table('exports')->insertUsing([
                'export_session_id',
                'export_name',
                'id_user',
                'id',
                'column1',
                'column2',
                'column3',
                'column4',
                'column5',
                'column6',
                'column7',
                'column8',
                'column9',
            ], $query->toBase());
        }, 3);
    }

    /**
     * @psalm-return ExportCsv::class
     */
    public function getExportCsvJob(): string
    {
        return ExportCsv::class;
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::findByNRP($this->userId);

        $this->updateSessionStatus('failed');

        Notification::make()
            ->message('Export data failed')
            ->danger()
            ->send($user);
    }
}
