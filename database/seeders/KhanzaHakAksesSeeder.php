<?php

namespace Database\Seeders;

use App\Models\Aplikasi\HakAkses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class KhanzaHakAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();
        HakAkses::truncate();

        $mapping = collect(config('khanza.mapping_akses'));

        $mapping->transform(fn (string $judul, string $field): array => [
            'nama_field'    => $field,
            'judul_menu'    => $judul,
            'default_value' => 'false',
        ]);

        HakAkses::insert($mapping->toArray());

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
}
