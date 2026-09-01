<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\ResepObat;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ListPenyerahan extends Component
{
    #[On('marqueePenyerahanFinished')]
    public function refreshData(): void
    {
        $this->dispatch('$refresh');
    }

    public function getDataPenyerahanProperty()
    {
        return ResepObat::query()->antreanFarmasiRawatJalan('penyerahan')->get();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.antrean-farmasi.list-penyerahan');
    }
}
