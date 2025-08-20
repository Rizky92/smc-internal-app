<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\ResepObat;
use Livewire\Component;

class ListPengerjaan extends Component
{
    protected $listeners = ['marqueePengerjaanFinished' => '$refresh'];

    public function getDataPengerjaanProperty()
    {
        return ResepObat::query()->antreanFarmasiRawatJalan('pengerjaan')->get();
    }

    public function render()
    {
        return view('livewire.pages.informasi.antrean-farmasi.list-pengerjaan');
    }
}
