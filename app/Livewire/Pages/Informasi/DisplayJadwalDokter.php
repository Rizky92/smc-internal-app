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
        ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.display-jadwal-dokter')
            ->layout(CustomerLayout::class, ['title' => 'Display Jadwal Dokter']);
    }
}

