<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\AntreanLoketFarmasi;
use Illuminate\View\View;
use Livewire\Component;

class NomorDipanggil extends Component
{
    public function getAntreanProperty(): ?AntreanLoketFarmasi
    {
        return AntreanLoketFarmasi::query()->terakhirDipanggilHariIni()->first();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.antrean-farmasi.nomor-dipanggil');
    }
}
