<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\ResepObat;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ListPengerjaan extends Component
{
    #[On('marqueePengerjaanFinished')]
    public function refreshData(): void
    {
        $this->dispatch('$refresh');
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
