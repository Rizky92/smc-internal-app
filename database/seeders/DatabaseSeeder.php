<?php

namespace Database\Seeders;

use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\Models\Keuangan\PiutangDilunaskan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            KhanzaHakAksesSeeder::class,
        ]);

        JurnalMedis::refreshModel();
        JurnalNonMedis::refreshModel();
        PiutangDilunaskan::refreshModel();
    }
}
