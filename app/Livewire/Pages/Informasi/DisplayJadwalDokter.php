<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Antrian\Jadwal;
use App\View\Components\CustomerLayout;
use Illuminate\View\View;
use Livewire\Component;

class DisplayJadwalDokter extends Component
{
    public function getDataJadwalDokterProperty()
    {
        return Jadwal::query()
            ->jadwalDokter()
            ->with(['dokter', 'poliklinik'])
            ->orderBY('jam_mulai')
            ->get() // Menggunakan get() untuk mengeksekusi query dan mendapatkan hasilnya dalam bentuk koleksi
            ->map(function ($item) {
                // Hitung total registrasi menggunakan fungsi pada model Jadwal
                [$total_registrasi_jadwal1, $total_registrasi_jadwal2] = Jadwal::hitungTotalRegistrasi(
                    $item->kd_dokter,
                    $item->kd_poli,
                    $item->hari_kerja,
                    now()->toDateString() // Ubah sesuai kebutuhan format tanggal
                );

                // Periksa apakah $item memiliki duplikat
                if ($item->isDuplicate()) {
                    // Ambil jadwal pertama dari hasil sortasi
                    $firstJadwal = Jadwal::where('kd_dokter', $item->kd_dokter)
                        ->where('kd_poli', $item->kd_poli)
                        ->where('hari_kerja', $item->hari_kerja)
                        ->orderBy('jam_mulai', 'asc')
                        ->first();

                    // Periksa apakah $item merupakan jadwal pertama atau kedua
                    if ($item->jam_mulai === $firstJadwal->jam_mulai) {
                        // Jadwal pertama, tampilkan sesuai kuota
                        $item->total_registrasi = min($total_registrasi_jadwal1, $item->kuota);
                    } else {
                        // Jadwal kedua, terus menjumlahkan sisanya
                        $item->total_registrasi = $total_registrasi_jadwal2;
                    }
                } else {
                    // Jika tidak ada duplikat, hitung total registrasi tanpa batasan kuota
                    $item->total_registrasi = $total_registrasi_jadwal1;
                }

                return $item;
            });
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.display-jadwal-dokter')
            ->layout(CustomerLayout::class, ['title' => 'Display Jadwal Dokter']);
    }
}
