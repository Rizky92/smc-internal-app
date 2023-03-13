<?php

namespace Database\Seeders;

use App\Models\Aplikasi\HakAkses;
use Illuminate\Database\Seeder;

class KhanzaHakAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mapping = collect(config('khanza.mapping_akses'));

        $mapping->transform(function ($judul, $field) {
            return [
                'nama_field' => $field,
                'judul_menu' => $judul,
                'default_value' => 'false',
            ];
        });

        HakAkses::insert($mapping->toArray());
    }
}
