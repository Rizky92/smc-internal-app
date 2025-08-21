<?php

namespace App\Livewire\Pages\Informasi\AntreanFarmasi;

use App\Models\Farmasi\ResepObat;
use Livewire\Component;

class ListPenyerahan extends Component
{
    protected $listeners = ['marqueePenyerahanFinished' => 'refreshData'];

    public function refreshData()
    {
        $this->emitSelf('$refresh');
    }
    
    public function getDataPenyerahanProperty()
    {
        return ResepObat::query()->antreanFarmasiRawatJalan('penyerahan')->get();
    }

    public function render()
    {
        return view('livewire.pages.informasi.antrean-farmasi.list-penyerahan');
    }
}
