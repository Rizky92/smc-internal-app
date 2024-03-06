<?php

namespace App\Livewire\Pages\Keuangan;

use Illuminate\View\View;
use App\Models\Keuangan\Rekening;
use App\View\Components\CustomerLayout;
use Livewire\Component;

class CetakPDFPostingJurnal extends Component
{
    public $savedData;

    public $rekeningData;

    public function mount()
    {
        $this->savedData = session('savedData');

        $this->rekeningData = Rekening::with('jurnal')->pluck('nm_rek', 'kd_rek');
    }

    public function render()
    {
        return view('livewire.pages.keuangan.cetak-p-d-f-posting-jurnal')
        ->layout(CustomerLayout::class, ['title' => 'Cetak PDF Posting Jurnal']);
    }
}
