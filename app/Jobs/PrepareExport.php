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

        /*
         * straight_join memaksa MariaDB menjalankan join sesuai urutan yang
         * ditulis. Tanpa itu optimizer memilih rekening sebagai tabel
         * penggerak, lalu menyebar ke detailjurnal lewat kd_rek, dan baru
         * menyaring tanggal saat menyentuh jurnal per baris. Akibatnya seluruh
         * detailjurnal terbaca berapa pun rentang tanggal yang diminta, dan
         * indeks tgl_jurnal tidak terpakai sama sekali.
         *
         * Penyebabnya statistik indeks yang meleset: cardinality kd_rek pada
         * detailjurnal tercatat 31.020 padahal nilai uniknya hanya 319, jadi
         * optimizer menaksir satu lookup hanya 992 baris (kenyataannya ~97.545)
         * dan menganggap rencana itu 74x lebih murah dari yang sebenarnya.
         *
         * Dengan jurnal sebagai penggerak, rentang tanggal dilayani range scan
         * pada indeks tgl_jurnal, detailjurnal dilayani indeks penutup
         * (no_jurnal, kd_rek), dan rekening cukup eq_ref lewat primary key.
         * Terukur 584,88s -> 38,51s untuk satu bulan dengan hasil sama persis.
         */
        /*
         * Dua kunci urutan terakhir mengikuti SIMRS Khanza: debet terbesar
         * lebih dulu, lalu kredit terbesar. Tanpa keduanya, baris detail di
         * dalam satu no_jurnal tidak punya urutan yang pasti, sehingga dua kali
         * export atas periode yang sama bisa menghasilkan susunan baris berbeda
         * dan menyulitkan saat hasilnya dibandingkan.
         *
         * kd_rek ditambahkan sebagai kunci terakhir karena debet dan kredit
         * saja belum cukup: pada data satu bulan masih ada 131.066 baris yang
         * nilainya kembar persis. Khanza pun tidak menentukan urutan untuk
         * baris-baris itu, jadi kunci ini hanya memastikan yang sebelumnya
         * tidak ditentukan menjadi tetap, tanpa mengubah urutan milik Khanza.
         */
        $kolom = <<<'SQL'
            straight_join
            ? as export_session_id,
            ? as export_name,
            ? as id_user,
            row_number() over (
                order by
                    jurnal.tgl_jurnal asc,
                    jurnal.jam_jurnal asc,
                    jurnal.no_jurnal asc,
                    detailjurnal.debet desc,
                    detailjurnal.kredit desc,
                    detailjurnal.kd_rek asc
            ) as id,
            jurnal.tgl_jurnal,
            jurnal.jam_jurnal,
            jurnal.no_jurnal,
            jurnal.no_bukti,
            jurnal.keterangan,
            detailjurnal.kd_rek,
            rekening.nm_rek,
            detailjurnal.debet,
            detailjurnal.kredit
            SQL;

        $query = Jurnal::on('mysql_sik')
            ->selectRaw($kolom, [$this->exportSessionId, $this->exportName, $this->userId])
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
