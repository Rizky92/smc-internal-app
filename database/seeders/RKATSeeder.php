<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RKATSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Bidang::truncate();
        Anggaran::truncate();
        AnggaranBidang::truncate();
        PemakaianAnggaran::truncate();

        Schema::enableForeignKeyConstraints();

        Bidang::insert([
            ['nama' => 'Keuangan'],
            ['nama' => 'Marketing'],
            ['nama' => 'Pelayanan Medis'],
            ['nama' => 'SDM'],
            ['nama' => 'Umum'],
        ]);

        Anggaran::insert([
            ['nama' => 'Kebutuhan Program Kerja', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Ketenagakerjaan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Umum', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Alkes', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kebutuhan Barang Obat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        AnggaranBidang::insert([
            ['bidang_id' => 1, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 58532122865, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1818732347, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 169253345, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 4962623, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 1, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 2, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 374035000, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1068214956, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 45289690, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 1561800, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 2, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 3, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 2641290000, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1866038114, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 35453084, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 26227062, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 3, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 4, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 6477214955, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 890426877, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 28989220, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 6127189, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 4, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],

            ['bidang_id' => 5, 'anggaran_id' => 1, 'tahun' => 2023, 'nominal_anggaran' => 9975789451, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 2, 'tahun' => 2023, 'nominal_anggaran' => 1598399150, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 3, 'tahun' => 2023, 'nominal_anggaran' => 623717072, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 4, 'tahun' => 2023, 'nominal_anggaran' => 6054475, 'created_at' => now(), 'updated_at' => now()],
            ['bidang_id' => 5, 'anggaran_id' => 5, 'tahun' => 2023, 'nominal_anggaran' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
