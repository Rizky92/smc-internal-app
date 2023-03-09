<?php

namespace Database\Seeders;

use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\Models\Keuangan\PiutangDilunaskan;
use Illuminate\Database\Seeder;

class SmcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JurnalMedis::refreshModel();
        JurnalNonMedis::refreshModel();
        PiutangDilunaskan::refreshModel();
    }
}
