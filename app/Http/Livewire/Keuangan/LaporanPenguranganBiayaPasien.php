<?php

namespace App\Http\Livewire\Keuangan;

use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPenguranganBiayaPasien extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public function render()
    {
        return view('livewire.keuangan.laporan-pengurangan-biaya-pasien');
    }
}
