<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use App\Models\Antrian\Jadwal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class JadwalDokter extends Component
{
    use FlashComponent, Filterable, LiveTable;

    /** @var bool */
    public $semuaPoli;

    protected function queryString(): array
    {
        return [
            'semuaPoli'     => ['except' => false, 'as' => 'tampilkan_semua_poli'],
        ];
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

    private function hitungRegistrasi($kdPoli, $kdDokter, $tanggal)
    {
        return RegistrasiPasien::hitungData($kdPoli, $kdDokter, $tanggal);
    }

    public function getDataJadwalDokterProperty()
    {
        $hari = now()->format('l');
        $namahari = $this->getNamaHari($hari);
        $jadwal=Jadwal::query()
            ->jadwalDokter()
            ->with(['dokter', 'poliklinik'])
            ->where('hari_kerja', $namahari);

        if (!$this->semuaPoli) {
            $jadwal->where('poliklinik.nm_poli', '<>', 'Poli Eksekutif');
        }

        $jadwal = $jadwal
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);

        $jadwal->transform(function ($jadwalItem) {
            $count = $this->hitungRegistrasi(
                $jadwalItem->poliklinik->kd_poli,
                $jadwalItem->dokter->kd_dokter,
                now()->format('Y-m-d')
            );
            $jadwalItem->register = $count;

            return $jadwalItem;
        });

        return $jadwal;
    }

    public function render(): View
    {
        $hari = now()->format('l');
        $namahari = $this->getNamaHari($hari);
        $jadwal = $this->getDataJadwalDokterProperty();
        return view('livewire.pages.informasi.jadwal-dokter', compact('jadwal', 'namahari'))
            ->layout(BaseLayout::class, ['title' => 'Jadwal Dokter Hari Ini']);
    }
 
     protected function defaultValues(): void
     {
        $this->semuaPoli = false;
     }
}

