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
use Illuminate\Support\Facades\Storage;
use Vtiful\Kernel\Excel;

/**
 * Export Buku Besar yang men-stream baris dari sik langsung ke xlsx.
 *
 * Menggantikan PrepareExport -> ExportCsv -> WriteExcel, yang menulis data
 * yang sama berkali-kali ke disk (tabel exports, payload jobs, shard CSV,
 * salinan xlsx). Di sini satu-satunya yang ditulis adalah file sementara
 * xlswriter dan file xlsx akhir, langsung di folder tujuan.
 *
 * Memakai ext-xlswriter mode constMemory (bukan wrapper ExcelExport, yang
 * memakai mode biasa dan menahan seluruh sel di memori). Setiap baris langsung
 * ditulis ke file sementara lewat tmpfile(), sehingga memori tetap datar
 * berapa pun jumlah barisnya; koneksi mysql_sik_export pun unbuffered.
 *
 * Folder file sementara tidak bisa diatur: ekstensi memanggil libxlsxwriter
 * dengan tmpdir NULL, jadi di Linux selalu di /tmp (TMPDIR pun diabaikan).
 * File tmpfile() langsung di-unlink, sehingga hilang sendiri saat proses mati.
 * Terukur untuk periode 2024 (~8,8 juta baris, 9 sheet): puncak ~5,9 GB,
 * 438 detik, xlsx 419 MB. Bandingkan OpenSpout: 11,78 GB, 1.199 detik.
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

    /**
     * Jumlah baris per panggilan data(), agar tidak menyeberang dari PHP ke
     * ekstensi untuk setiap baris.
     */
    private const ROWS_PER_WRITE = 1000;

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

        $this->ensureEnoughDiskSpace(sys_get_temp_dir());

        $sheet = 1;

        /**
         * Argumen ketiga use_zip64 (default true) ada sejak ext-xlswriter
         * 1.5, tetapi stub Psalm belum mengenalnya. Zip64 dimatikan agar file
         * terbuka di lebih banyak versi Excel; setiap sheet dibatasi 1.048.576
         * baris (~700 MB XML), jauh di bawah batas 4 GB per entri zip.
         *
         * @psalm-suppress TooManyArguments
         */
        $file = (new Excel(['path' => $disk->path($this->getFileDirectory())]))
            ->constMemory(basename($filePath), 'Sheet'.$sheet, false);

        // Pergantian sheet diatur sendiri agar setiap sheet diawali header.
        $file->header($this->columnHeaders);
        $rowsInSheet = 1;

        $buffer = [];

        foreach ($this->query()->cursor() as $record) {
            if ($rowsInSheet >= $this->maxRowsPerSheet) {
                $this->flush($file, $buffer);

                $file->addSheet('Sheet'.(++$sheet));
                $file->header($this->columnHeaders);
                $rowsInSheet = 1;
            }

            $buffer[] = array_values((array) $record);
            $rowsInSheet++;

            if (count($buffer) >= self::ROWS_PER_WRITE) {
                $this->flush($file, $buffer);
            }
        }

        $this->flush($file, $buffer);

        $file->output();

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
     * @param  array<int, array>  $buffer
     */
    private function flush(Excel $file, array &$buffer): void
    {
        if ($buffer === []) {
            return;
        }

        $file->data($buffer);

        $buffer = [];
    }

    /**
     * Gagal sejak awal bila ruang kosong di folder sementara tidak cukup,
     * daripada disk penuh di tengah jalan. Bila ruang kosong tidak bisa
     * dibaca, export tetap dijalankan.
     */
    private function ensureEnoughDiskSpace(string $folder): void
    {
        $minimum = (float) config('export.min_free_gb') * 1024 ** 3;

        $free = disk_free_space($folder);

        if ($free !== false && $free < $minimum) {
            throw new \RuntimeException(sprintf(
                'Ruang kosong di %s hanya %.1f GB, export Buku Besar membutuhkan minimal %.1f GB.',
                $folder,
                $free / 1024 ** 3,
                $minimum / 1024 ** 3
            ));
        }
    }

    public function failed(\Throwable $exception): void
    {
        /*
         * Pembersihan sengaja di sini, bukan di catch pada handle(): saat job
         * melewati timeout, worker memanggil failed() lalu mematikan proses,
         * sehingga catch/finally di handle() tidak pernah berjalan. failed()
         * juga dipanggil pada instance baru hasil unserialize, jadi semua path
         * diturunkan ulang dari parameter job. Folder session hanya berisi xlsx
         * milik job ini, jadi aman dihapus seluruhnya. File sementara
         * xlswriter tidak perlu dihapus: tmpfile() hilang saat proses mati.
         */
        Storage::disk('public')->deleteDirectory($this->getFileDirectory());

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
