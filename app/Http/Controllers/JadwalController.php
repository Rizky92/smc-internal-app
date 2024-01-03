<?php

namespace App\Http\Controllers;

use App\Models\Antrian\Jadwal;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\View\View;

class JadwalController
{
    public function jadwal(): View
    {
        $hari = now()->format('l');
        $namahari = $this->getNamaHari($hari);

        $jadwal = Jadwal::with(['dokter', 'poliklinik'])
            ->where('hari_kerja', $namahari)
            ->get();

        $tanggal = now();

        foreach ($jadwal as $jadwalItem) {
            $count = RegistrasiPasien::hitungData(
                $jadwalItem->kd_poli,
                $jadwalItem->kd_dokter,
                $tanggal
            );
            $jadwalItem->register = $count;
        }

        return view('jadwal', compact('jadwal', 'namahari'));
    }

    private function getNamaHari(string $hari): string
    {
        switch ($hari) {
            case 'Sunday':
                return 'AKHAD';
            case 'Monday':
                return 'SENIN';
            case 'Tuesday':
                return 'SELASA';
            case 'Wednesday':
                return 'RABU';
            case 'Thursday':
                return 'KAMIS';
            case 'Friday':
                return 'JUMAT';
            case 'Saturday':
                return 'SABTU';
            default:
                return '';
        }
    }
}
