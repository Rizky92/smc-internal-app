<?php

namespace App\Livewire\Pages\Informasi;

use App\View\Components\CustomerLayout;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Antrian\Jadwal;
use Illuminate\View\View;
use Livewire\Component;

class DisplayJadwalDokter extends Component
{
    public function getDataJadwalDokterProperty()
    {
        return Jadwal::query()
            ->jadwalDokter()
            ->with(['dokter', 'poliklinik'])
            ->get() // Menggunakan get() untuk mengeksekusi query dan mendapatkan hasilnya dalam bentuk koleksi
            ->map(function ($item) {
                // Hitung total registrasi menggunakan fungsi pada model Jadwal
                [$total_registrasi_jadwal1, $total_registrasi_jadwal2] = Jadwal::hitungTotalRegistrasi(
                    $item->kd_dokter,
                    $item->kd_poli,
                    $item->hari_kerja,
                    now()->format('Y-m-d') // Ubah sesuai kebutuhan format tanggal
                );

                // Periksa apakah saat ini adalah jadwal pertama atau kedua
                $current_jadwal = ($item->jam_mulai <= now()->format('H:i:s')) ? $total_registrasi_jadwal1 : $total_registrasi_jadwal2;

                // Update property pada objek item dengan total registrasi yang sesuai
                $item->total_registrasi = $current_jadwal;

                return $item;
            });
    }
    

    public function render(): View
    {
        return view('livewire.pages.informasi.display-jadwal-dokter')
            ->layout(CustomerLayout::class, ['title' => 'Display Jadwal Dokter']);
    }
}

