<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siap:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup instalasi SIAP untuk pengembangan lokal';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
