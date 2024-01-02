<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Bangsal;
use App\View\Components\CustomerLayout;
use Illuminate\View\View;
use Livewire\Component;

class InformasiKamar extends Component
{
    public function getDataInformasiKamarProperty()
    {
        return Bangsal::with('kamar')
            ->activeWithKamar()
            ->distinct()
            ->orderBy('nm_bangsal')
            ->orderBy('kelas')
            ->get();
    }

    public function render(): View
    {
        $informasiKamar = $this->getDataInformasiKamarProperty();
        $kelasList = Bangsal::getKelasList();

        return view('livewire.pages.informasi.informasi-kamar', compact('informasiKamar', 'kelasList'))
            ->layout(CustomerLayout::class, ['title' => 'Informasi Kamar']);
    }
}
