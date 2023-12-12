<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Antrian\Jadwal;
use Illuminate\View\View;
use Livewire\Component;

class JadwalDokter extends Component
{

    public function getDataJadwalDokterProperty(): Paginator
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
    }

    private function getNamaHari($hari)
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

    public function render(): View
    {
        return view('livewire.pages.informasi.jadwal-dokter', compact('namahari'));
    }

    protected function defaultValues(): void
    {
        
    }

    protected function pageHeaders(): array
    {
        //
    }

    protected function columnHeaders(): array
    {
        //
    }   

    protected function dataPerSheet(): array
    {
        //
    }
}

