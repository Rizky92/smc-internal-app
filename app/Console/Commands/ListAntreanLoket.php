<?php

namespace App\Console\Commands;

use App\Events\PanggilAntreanLoketSmc;
use App\Events\StopAntreanLoketSmc;
use App\Services\AntreanLoketSmcWatcher;
use Illuminate\Console\Command;

class ListAntreanLoket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'antrean-loket:listen-antrean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen antrean loket dari SIMRS Khanza';

    /**
     * Store the last hash to detect changes.
     *
     * @var string|null
     */
    private $lastHash = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(AntreanLoketSmcWatcher $watcher): void
    {
        $this->info('Listening antrean loket changes...');

        while (true) {
            $current = $watcher->current();

            if ($current) {
                $this->info('DB READ: '.json_encode($current));

                $hash = md5($current->loket.$current->antrian);

                if ($this->lastHash !== $hash) {
                    $this->info("EVENT: Panggil antrean loket {$current->loket} - {$current->antrian}");

                    event(new PanggilAntreanLoketSmc(
                        $current->loket,
                        $current->antrian
                    ));

                    $this->lastHash = $hash;
                }
            } else {
                $this->info('DB EMPTY');

                if ($this->lastHash !== null) {
                    event(new StopAntreanLoketSmc);
                    $this->lastHash = null;
                }
            }

            usleep(500000);
        }
    }
}
