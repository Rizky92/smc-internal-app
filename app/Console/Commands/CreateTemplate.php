<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siap:make
        {name : Name of the template to be created}
        {--from-schema : Use the schema from given name value}
        {--model= : generate associated model with given name. If empty, will use name value instead}
        {--livewire= : generate associated Livewire page with given name. If empty, will use name value instead}
        {--test= : generate associated feature test with given name. If empty, will use name value instead}
        {--migration= : generate associated migration file with given name. If empty, will use name value instead}
        {--seeder= : generate associated seeder and its factory with given name. If empty, will use name value instead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a template file';

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
        return 0;
    }
}
