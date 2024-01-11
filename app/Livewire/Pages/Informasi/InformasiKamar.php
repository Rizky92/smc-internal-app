<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Bangsal;
use App\View\Components\CustomerLayout;
use App\Models\Bangsal;
use Illuminate\View\View;
use Livewire\Component;

class InformasiKamar extends Component
{
    public function getDataInformasiKamarProperty()
    {
        return Bangsal::query()
        ->informasiKamar()
        ->with('kamar')
        ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.informasi-kamar')
            ->layout(CustomerLayout::class, ['title' => 'Informasi Kamar']);
    }

}
