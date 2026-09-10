<?php

namespace App\Console\Commands;

use App\Models\ExportSession;
use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CleanupExports extends Command
{
    protected $signature = 'exports:cleanup
                            {--hours= : Override masa tenggang (jam) sebelum artefak boleh dibersihkan}
                            {--force : Abaikan masa tenggang, bersihkan semuanya}
                            {--dry-run : Hanya laporkan apa yang akan dihapus, tanpa menghapus}';

    protected $description = 'Membersihkan file hasil export, tabel staging, sesi export, dan notifikasi export';

    /**
     * Batas waktu: artefak yang lebih tua dari ini akan dibersihkan. Bernilai
     * null kalau --force dipakai, yang berarti semuanya dibersihkan.
     */
    private ?Carbon $cutoff = null;

    private bool $dryRun = false;

    private int $chunk = 500;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $this->chunk = (int) config('exports.cleanup.chunk', 500);

        $hoursOption = $this->option('hours');

        $hours = is_numeric($hoursOption)
            ? (int) $hoursOption
            : (int) config('exports.cleanup.grace_hours', 24);

        $this->cutoff = $this->option('force') ? null : now()->subHours($hours);

        $this->info($this->dryRun ? 'Mode dry-run: tidak ada yang dihapus.' : 'Membersihkan artefak export...');
        $this->line($this->cutoff
            ? 'Artefak yang lebih tua dari '.$this->cutoff->toDateTimeString().' akan dibersihkan.'
            : 'Masa tenggang diabaikan (--force): semua artefak export akan dibersihkan.');

        /*
         * Sesi yang masih dalam masa tenggang. Statusnya sengaja tidak difilter:
         * sesi yang macet di pending/processing memang termasuk sasaran cleanup,
         * sehingga umur sesi adalah satu-satunya penentu. Daftar inilah yang
         * dipertahankan, baik barisnya di tabel staging maupun direktori filenya.
         */
        $activeSessionIds = $this->activeSessionIds();

        $summary = [
            ['Baris tabel staging `exports`', $this->cleanupStagingRows($activeSessionIds)],
            ['Direktori export background', $this->cleanupBackgroundFiles($activeSessionIds)],
            ['File export sinkron', $this->cleanupSyncFiles()],
            ['Baris `export_sessions`', $this->cleanupSessions()],
            ['Notifikasi export', $this->cleanupNotifications()],
        ];

        $this->newLine();
        $this->table(['Artefak', $this->dryRun ? 'Akan dihapus' : 'Dihapus'], $summary);

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function activeSessionIds(): Collection
    {
        if (! $this->cutoff) {
            return collect();
        }

        return ExportSession::query()
            ->where('updated_at', '>=', $this->cutoff)
            ->pluck('session_id');
    }

    /**
     * Tabel `exports` tidak punya kolom created_at, jadi umur barisnya tidak bisa
     * dipakai. Aturannya dibalik: baris yang sesinya tidak lagi aktif ikut dibuang.
     *
     * @param  Collection<int, string>  $activeSessionIds
     */
    private function cleanupStagingRows(Collection $activeSessionIds): int
    {
        /*
         * Koneksi mysql_smc_export memakai isolation level READ COMMITTED, dan
         * penghapusannya dipecah per batch supaya ribuan baris tidak dihapus
         * dalam satu transaksi raksasa yang mengunci tabel selama export lain
         * sedang menulis.
         */
        $query = fn () => DB::connection('mysql_smc_export')
            ->table('exports')
            ->whereNotIn('export_session_id', $activeSessionIds->all());

        if ($this->dryRun) {
            return $query()->count();
        }

        $total = 0;

        do {
            $deleted = $query()->limit($this->chunk)->delete();

            $total += $deleted;
        } while ($deleted > 0);

        return $total;
    }

    /**
     * Hapus direktori exports/{nik}/{sessionId} milik sesi yang sudah tidak aktif,
     * termasuk shard CSV, headers.csv, dan file .xlsx hasil akhirnya.
     *
     * @param  Collection<int, string>  $activeSessionIds
     */
    private function cleanupBackgroundFiles(Collection $activeSessionIds): int
    {
        $disk = $this->disk();
        $basePath = config('exports.directories.background');

        $total = 0;

        foreach ($disk->directories($basePath) as $userDirectory) {
            foreach ($disk->directories($userDirectory) as $sessionDirectory) {
                if ($activeSessionIds->contains(basename($sessionDirectory))) {
                    continue;
                }

                if (! $this->dryRun) {
                    $disk->deleteDirectory($sessionDirectory);
                }

                $total++;
            }

            /*
             * Direktori per user hanya berisi direktori sesi. Kalau sudah kosong
             * setelah pembersihan, tidak ada gunanya dibiarkan menumpuk.
             */
            if (! $this->dryRun
                && empty($disk->directories($userDirectory))
                && empty($disk->files($userDirectory))) {
                $disk->deleteDirectory($userDirectory);
            }
        }

        return $total;
    }

    /**
     * File di direktori `excel/` dihasilkan ExcelExportJob dan trait
     * ExcelExportable. Keduanya tidak punya sesi, jadi umur file yang dipakai.
     */
    private function cleanupSyncFiles(): int
    {
        $disk = $this->disk();
        $basePath = config('exports.directories.sync');

        $total = 0;

        foreach ($disk->files($basePath) as $file) {
            if ($this->cutoff && $disk->lastModified($file) >= $this->cutoff->getTimestamp()) {
                continue;
            }

            if (! $this->dryRun) {
                $disk->delete($file);
            }

            $total++;
        }

        return $total;
    }

    /**
     * Dijalankan setelah pembersihan file, karena tahap sebelumnya membaca daftar
     * sesi. Menghapus baris di sini juga membuka blokir user yang sesinya macet,
     * karena guard di halaman export menolak export baru selama masih ada sesi
     * berstatus pending atau processing.
     */
    private function cleanupSessions(): int
    {
        $query = ExportSession::query()
            ->when($this->cutoff, fn ($query) => $query->where('updated_at', '<', $this->cutoff));

        if ($this->dryRun) {
            return $query->count();
        }

        return $query->delete();
    }

    /**
     * Kolom `data` bertipe text berisi JSON, sehingga operator JSON path MySQL
     * tidak bisa dipakai dan pencocokan LIKE atas JSON ter-escape terlalu rapuh.
     * Kandidat diambil berdasarkan umur, lalu disaring di PHP.
     */
    private function cleanupNotifications(): int
    {
        $messagePrefixes = config('exports.notification.message_prefixes', []);
        $filePrefixes = config('exports.notification.file_prefixes', []);

        $ids = [];

        MultiConnectionDatabaseNotification::query()
            ->when($this->cutoff, fn ($query) => $query->where('created_at', '<', $this->cutoff))
            ->chunkById($this->chunk, function (Collection $notifications) use (&$ids, $messagePrefixes, $filePrefixes): void {
                foreach ($notifications as $notification) {
                    $data = (array) $notification->data;

                    $isExport = Str::startsWith((string) ($data['message'] ?? ''), $messagePrefixes)
                        || Str::startsWith((string) ($data['file'] ?? ''), $filePrefixes);

                    if ($isExport) {
                        $ids[] = $notification->getKey();
                    }
                }
            });

        if ($this->dryRun) {
            return count($ids);
        }

        $total = 0;

        foreach (array_chunk($ids, $this->chunk) as $chunkOfIds) {
            $total += MultiConnectionDatabaseNotification::query()
                ->whereIn('id', $chunkOfIds)
                ->delete();
        }

        return $total;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk(config('exports.disk'));
    }
}
