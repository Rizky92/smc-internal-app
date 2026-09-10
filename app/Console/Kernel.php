<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('antrean:clean-pintu')->timezone('Asia/Singapore')->daily();

        /*
         * Menghapus ribuan file shard bisa berjalan lama, sehingga cleanup
         * dijalankan di background dan dikunci agar tidak tumpang tindih dengan
         * eksekusi sebelumnya yang belum selesai.
         */
        $schedule->command('exports:cleanup')
            ->timezone('Asia/Singapore')
            ->dailyAt('01:00')
            ->withoutOverlapping(120)
            ->runInBackground();

        // Sisa metadata queue dari batch export yang sudah selesai maupun gagal.
        $schedule->command('queue:prune-batches --hours=48')
            ->timezone('Asia/Singapore')
            ->dailyAt('01:30');

        $schedule->command('queue:prune-failed --hours=168')
            ->timezone('Asia/Singapore')
            ->dailyAt('01:40');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
