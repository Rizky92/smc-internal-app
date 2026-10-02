<?php

namespace Database\Seeders;

use App\Models\Akreditasi\FocusArea;
use App\Models\Akreditasi\ProofMethod;
use Illuminate\Database\Seeder;

class AkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        ProofMethod::insert([
            ['kode' => 'R', 'nama' => 'Rekam Medik / Dokumen Administrasi'],
            ['kode' => 'D', 'nama' => 'Dokumentasi / Kebijakan & Prosedur'],
            ['kode' => 'O', 'nama' => 'Observasi / Wawancara'],
            ['kode' => 'W', 'nama' => 'Wawancara dengan Staf'],
            ['kode' => 'S', 'nama' => 'Survei / Kuesioner'],
            ['kode' => 'PK', 'nama' => 'Presentasi Kasus / Clinical Pathway'],
        ]);

        FocusArea::insert([
            [
                'kode'      => 'TKRS',
                'nama'      => 'Tata Kelola Rumah Sakit',
                'deskripsi' => 'Penyelenggaraan tata kelola rumah sakit yang baik',
                'urutan'    => 1,
            ],
            [
                'kode'      => 'KPS',
                'nama'      => 'Keselamatan Pasien',
                'deskripsi' => 'Sasaran keselamatan pasien di rumah sakit',
                'urutan'    => 2,
            ],
            [
                'kode'      => 'MFK',
                'nama'      => 'Manajemen Fasilitas & Keselamatan',
                'deskripsi' => 'Pengelolaan fasilitas dan keselamatan lingkungan',
                'urutan'    => 3,
            ],
            [
                'kode'      => 'PMKP',
                'nama'      => 'Peningkatan Mutu & Kinerja',
                'deskripsi' => 'Program peningkatan mutu dan kinerja rumah sakit',
                'urutan'    => 4,
            ],
            [
                'kode'      => 'MRMIK',
                'nama'      => 'Manajemen Rekam Medis & Informasi Kesehatan',
                'deskripsi' => 'Pengelolaan rekam medis dan sistem informasi kesehatan',
                'urutan'    => 5,
            ],
            [
                'kode'      => 'SKP',
                'nama'      => 'Sasaran Keselamatan Pasien',
                'deskripsi' => 'Sasaran-sasaran spesifik keselamatan pasien (SKP)',
                'urutan'    => 6,
            ],
            [
                'kode'      => 'HPK',
                'nama'      => 'Hak Pasien & Keluarga',
                'deskripsi' => 'Pemenuhan hak pasien dan keluarga',
                'urutan'    => 7,
            ],
            [
                'kode'      => 'AP',
                'nama'      => 'Akses ke Pelayanan & Kontinuitas Pelayanan',
                'deskripsi' => 'Akses pasien terhadap pelayanan dan kontinuitas pelayanan',
                'urutan'    => 8,
            ],
            [
                'kode'      => 'PP',
                'nama'      => 'Pelayanan Pasien',
                'deskripsi' => 'Pelayanan kepada pasien yang aman dan bermutu',
                'urutan'    => 9,
            ],
            [
                'kode'      => 'PK',
                'nama'      => 'Pendidikan & Kualifikasi Staf',
                'deskripsi' => 'Pendidikan dan kualifikasi tenaga kesehatan',
                'urutan'    => 10,
            ],
            [
                'kode'      => 'PAB',
                'nama'      => 'Pencegahan & Pengendalian Infeksi',
                'deskripsi' => 'Program pencegahan dan pengendalian infeksi di rumah sakit',
                'urutan'    => 11,
            ],
        ]);
    }
}
