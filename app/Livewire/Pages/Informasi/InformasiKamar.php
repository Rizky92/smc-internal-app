<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Perawatan\Kamar;
use App\Models\Bangsal;
use Illuminate\View\View;
use Livewire\Component;

class InformasiKamar extends Component
{
    public function getDataInformasiKamarProperty()
    {
        return Bangsal::activeWithKamar()
            ->distinct()
            ->orderBy('nm_bangsal')
            ->orderBy('kelas')
            ->paginate(200);
    }

    public function render(): View
    {
        $informasiKamar = $this->getDataInformasiKamarProperty();
        return view('livewire.pages.informasi.informasi-kamar',  compact('informasiKamar'));
    }
}
