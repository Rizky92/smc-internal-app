<?php

namespace App\Livewire\Pages\Informasi;

use App\View\Components\CustomerLayout;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Antrian\Jadwal;
use Illuminate\View\View;
use Livewire\Component;

class DisplayJadwalDokter extends Component
{
    private function getNamaHari($hari): string
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

    private function hitungRegistrasi($kdPoli, $kdDokter, $tanggal): int
    {
        return RegistrasiPasien::hitungData($kdPoli, $kdDokter, $tanggal);
    }

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Collection<\Illuminate\Database\Eloquent\Model>
     */
    public function getDataJadwalDokterProperty(): \Illuminate\Database\Eloquent\Collection
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

        return $jadwal;
    }

    public function render(): View
    {
        $hari = now()->format('l');
        $namahari = $this->getNamaHari($hari);
        $jadwal = Jadwal::with(['dokter', 'poliklinik'])
            ->where('hari_kerja', $namahari)
            ->get();

        foreach ($jadwal as $jadwalItem) {
                $jadwalItem->register = $this->hitungRegistrasi(
                $jadwalItem->kd_poli,
                $jadwalItem->kd_dokter,
                now()->format('Y-m-d')
            );
        }

        return view('livewire.pages.informasi.display-jadwal-dokter', compact('jadwal', 'namahari'))
            ->layout(CustomerLayout::class, ['title' => 'Display Jadwal Dokter']);
    }
}

