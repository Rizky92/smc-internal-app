<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\ResepObat;
use Illuminate\View\View;
use Livewire\Component;

class ListPengerjaan extends Component
{
    protected $listeners = ['marqueePengerjaanFinished' => 'refreshData'];

    public function refreshData(): void
    {
        $this->emitSelf('$refresh');
    }

    public function getDataPengerjaanProperty()
    {
        return ResepObat::query()->antreanFarmasiRawatJalan('pengerjaan')->get();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.antrean-farmasi.list-pengerjaan');
    }
}
