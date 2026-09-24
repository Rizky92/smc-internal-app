<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesExportSession;
use App\Models\Aplikasi\User;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Export Buku Besar yang men-stream baris dari sik langsung ke xlsx.
 *
 * Menggantikan PrepareExport -> ExportCsv -> WriteExcel, yang menulis data
 * yang sama berkali-kali ke disk (tabel exports, payload jobs, shard CSV,
 * salinan xlsx). Di sini satu-satunya yang ditulis adalah XML sementara
 * OpenSpout (diarahkan ke export.temp_dir, di server berupa tmpfs) dan file
 * xlsx akhir, langsung di folder tujuan.
 *
 * Memori tetap datar berapa pun jumlah barisnya: koneksi mysql_sik_export
 * unbuffered sehingga baris dibaca satu per satu, dan OpenSpout menulis setiap
 * baris langsung ke file sementara.
 */
class BukuBesarExport implements ShouldQueue
{
    use Dispatchable;
    use HandlesExportSession;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Batas baris per worksheet xlsx, termasuk baris header.
     */
    public const MAX_ROWS_PER_SHEET = 1048576;

    public $tries = 1;

    public $timeout = 3600;

    private string $tglAwal;

    private string $tglAkhir;

    private string $kodeRekening;

    private array $columnHeaders;

    private int $maxRowsPerSheet;

    /**
     * @param  array{
     *      exportSessionId: string,
     *      exportName: string,
     *      userId: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      kodeRekening?: string,
     *      columnHeaders: array,
     *      maxRowsPerSheet?: int,
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
        $this->maxRowsPerSheet = $params['maxRowsPerSheet'] ?? self::MAX_ROWS_PER_SHEET;
    }

    public function handle(): void
    {
        $this->updateSessionStatus('processing');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $filePath = $this->getFileDirectory().'/'.now()->format('Y-m-d_H-i-s')."_{$this->userId}_{$this->exportSessionId}_{$this->exportName}.xlsx";

        $disk->makeDirectory($this->getFileDirectory());

        $tempFolder = $this->getTempFolder();

        File::ensureDirectoryExists($tempFolder);

        $options = new Options;
        $options->setTempFolder($tempFolder);
        // Pergantian sheet diatur sendiri agar setiap sheet diawali header.
        $options->SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY = false;

        $writer = new Writer($options);

        try {
            $writer->openToFile($disk->path($filePath));

            $header = Row::fromValues($this->columnHeaders);

            $writer->addRow($header);
            $rowsInSheet = 1;

            foreach ($this->query()->cursor() as $record) {
                if ($rowsInSheet >= $this->maxRowsPerSheet) {
                    $writer->addNewSheetAndMakeItCurrent();
                    $writer->addRow($header);
                    $rowsInSheet = 1;
                }

                $writer->addRow(Row::fromValues(array_values((array) $record)));
                $rowsInSheet++;
            }

            $writer->close();
        } finally {
            File::deleteDirectory($tempFolder);
        }

        $this->updateSessionStatus('completed');

        $this->notify(
            Notification::make()
                ->message('Export data is ready for download')
                ->filePath($filePath)
                ->success()
        );
    }

    /**
     * straight_join memaksa jurnal menjadi tabel penggerak. Tanpa itu
     * optimizer memilih rekening sebagai penggerak karena cardinality kd_rek
     * pada detailjurnal tercatat meleset (31.020, padahal nilai uniknya 319),
     * sehingga seluruh detailjurnal terbaca berapa pun rentang tanggalnya dan
     * indeks tgl_jurnal tidak terpakai. Terukur 584,88s -> 38,51s untuk satu
     * bulan dengan hasil sama persis.
     *
     * Urutan mengikuti SIMRS Khanza: debet terbesar lalu kredit terbesar di
     * dalam satu no_jurnal. kd_rek menjadi kunci terakhir karena masih ada
     * ratusan ribu baris yang debet dan kreditnya kembar persis, supaya dua
     * kali export atas periode yang sama selalu menghasilkan susunan yang sama.
     */
    private function query(): Builder
    {
        $connection = DB::connection('mysql_sik_export');

        // Server memutus koneksi bila tidak bisa mengirim baris selama
        // net_write_timeout (default 60 detik), misalnya saat worker lambat
        // membaca. Dinaikkan setara timeout job.
        $connection->statement('set session net_write_timeout = '.$this->timeout);

        return $connection->table('jurnal')
            ->selectRaw(<<<'SQL'
                straight_join
                jurnal.tgl_jurnal,
                jurnal.jam_jurnal,
                jurnal.no_jurnal,
                jurnal.no_bukti,
                jurnal.keterangan,
                detailjurnal.kd_rek,
                rekening.nm_rek,
                detailjurnal.debet,
                detailjurnal.kredit
                SQL)
            ->join('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($this->kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $this->kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$this->tglAwal, $this->tglAkhir])
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal')
            ->orderBy('jurnal.no_jurnal')
            ->orderByDesc('detailjurnal.debet')
            ->orderByDesc('detailjurnal.kredit')
            ->orderBy('detailjurnal.kd_rek');
    }

    public function getFileDirectory(): string
    {
        return "exports/{$this->userId}/{$this->exportSessionId}";
    }

    /**
     * Folder sementara OpenSpout untuk job ini. Namanya diturunkan dari
     * session supaya failed() bisa menemukannya lagi; OpenSpout sendiri hanya
     * membersihkannya lewat close().
     */
    public function getTempFolder(): string
    {
        return rtrim(strval(config('export.temp_dir')), '/\\').DIRECTORY_SEPARATOR.'export-'.$this->exportSessionId;
    }

    public function failed(\Throwable $exception): void
    {
        /*
         * Pembersihan sengaja di sini, bukan di catch pada handle(): saat job
         * melewati timeout, worker memanggil failed() lalu mematikan proses,
         * sehingga catch/finally di handle() tidak pernah berjalan. failed()
         * juga dipanggil pada instance baru hasil unserialize, jadi semua path
         * diturunkan ulang dari parameter job. Folder session hanya berisi xlsx
         * milik job ini, jadi aman dihapus seluruhnya.
         */
        Storage::disk('public')->deleteDirectory($this->getFileDirectory());
        File::deleteDirectory($this->getTempFolder());

        $this->updateSessionStatus('failed');

        $this->notify(
            Notification::make()
                ->message('Export data failed')
                ->danger()
        );
    }

    private function notify(Notification $notification): void
    {
        $user = User::findByNRP($this->userId);

        if ($user) {
            $notification->send($user);
        }
    }
}
