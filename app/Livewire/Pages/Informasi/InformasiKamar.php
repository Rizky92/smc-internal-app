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

    public function countEmptyRooms($kdBangsal)
    {
        return Kamar::where('statusdata', '1')
            ->where('kd_bangsal', $kdBangsal)
            ->where('status', 'KOSONG')
            ->count();
    }

    public function countOccupiedRooms($kdBangsal)
    {
        return Kamar::where('statusdata', '1')
            ->where('kd_bangsal', $kdBangsal)
            ->where('status', 'ISI')
            ->count();
    }

    public function render(): View
    {
        $informasiKamar = $this->getDataInformasiKamarProperty();
        return view('livewire.pages.informasi.informasi-kamar',  compact('informasiKamar'));
    }

    public function refetch()
    {
        $this->getDataInformasiKamarProperty();
        $this->emit('refreshData');
    }
    
    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        //
    }

    protected function columnHeaders(): array
    {
        //
    }

    protected function pageHeaders(): array
    {
        //
    }
}
