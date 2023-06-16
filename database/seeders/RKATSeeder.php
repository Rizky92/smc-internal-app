<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranDetail;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RKATSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        Bidang::truncate();
        Anggaran::truncate();
        AnggaranDetail::truncate();
        PemakaianAnggaran::truncate();

        Schema::enableForeignKeyConstraints();

        Bidang::factory()
            ->count(4)
            ->has(Anggaran::factory()
                ->count(4)
                ->has(AnggaranDetail::factory()
                    ->count(5)
                    ->has(PemakaianAnggaran::factory()
                        ->count(12), 'pemakaian'
                    ), 'detail'
                ), 'anggaran'
            )
            ->create();
    }
}
