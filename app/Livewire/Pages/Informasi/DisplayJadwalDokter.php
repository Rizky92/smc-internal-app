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
            ->get();
    }
    

    public function render(): View
    {
        return view('livewire.pages.informasi.display-jadwal-dokter')
            ->layout(CustomerLayout::class, ['title' => 'Display Jadwal Dokter']);
    }
}
