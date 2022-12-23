<?php

namespace App\Http\Livewire\Farmasi;

use Livewire\Component;
use Livewire\WithPagination;

class KunjunganFarmasiPasienPerPoli extends Component
{
    use WithPagination;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    protected $listeners = [
        'beginExcelExport',
    ];

    public function render()
    {
        return view('livewire.farmasi.kunjungan-farmasi-pasien-per-poli');
    }
}
