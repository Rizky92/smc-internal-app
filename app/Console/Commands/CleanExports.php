<?php

namespace App\Console\Commands;

use App\Models\ExportSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:clean {--hours= : Masa simpan dalam jam, default config export.retention_hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus folder hasil export yang sudah melewati masa simpan';

    public function handle(): int
    {
        $hours = $this->option('hours');
        $hours = is_numeric($hours) ? (int) $hours : (int) config('export.retention_hours');

        $batas = now()->subHours($hours);

        $disk = Storage::disk('public');

        foreach ($disk->directories('exports') as $userDirectory) {
            foreach ($disk->directories($userDirectory) as $sessionDirectory) {
                $session = ExportSession::query()
                    ->where('session_id', basename($sessionDirectory))
                    ->first();

                if ($session && in_array($session->status, ['pending', 'processing'], true)) {
                    continue;
                }

                if ($session) {
                    $terakhirDiubah = optional($session->updated_at)->getTimestamp() ?? 0;
                } else {
                    /*
                     * Folder sisa pipeline lama tidak selalu punya session,
                     * jadi umurnya diukur dari file yang terakhir ditulis.
                     */
                    $terakhirDiubah = collect($disk->allFiles($sessionDirectory))
                        ->map(fn (string $file) => $disk->lastModified($file))
                        ->max() ?? 0;
                }

                if ($terakhirDiubah > $batas->getTimestamp()) {
                    continue;
                }

                $disk->deleteDirectory($sessionDirectory);

                if ($session) {
                    $session->update(['status' => 'expired']);
                }
            }
        }

        return self::SUCCESS;
    }
}
