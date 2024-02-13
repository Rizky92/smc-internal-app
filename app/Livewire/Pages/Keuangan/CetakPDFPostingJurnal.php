<?php

namespace App\Livewire\Pages\Keuangan;

use Illuminate\View\View;
use Livewire\Component;

class CetakPDFPostingJurnal extends Component
{
    public $data;

    protected $listeners = ['printPdf'];

    public function render()
    {
        return view('livewire.pages.keuangan.cetak-p-d-f-posting-jurnal');
    }

    public function printPdf()
    {
        $this->emit('openPrintWindow');
    }
}
