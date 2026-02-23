<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAntreanPintu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'antrean:clean-pintu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean antrean pintu entries with status 0 from the database';

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
     *
     * @return int
     */
    public function handle()
    {
        DB::connection('mysql_sik')->table('antripintu_smc')->where('status', '0')->delete();

        return Command::SUCCESS;
    }
}
