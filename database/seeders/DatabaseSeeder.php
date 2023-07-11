<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();

        $this->call([
            PermissionSeeder::class,
            KhanzaHakAksesSeeder::class,
            SmcSeeder::class,
            RKATSeeder::class,
        ]);

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
}
