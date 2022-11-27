<?php

namespace App\Http\Livewire;

use App\Registrasi;
use Livewire\Component;
use Livewire\WithPagination;

class RekamMedisDataTableComponent extends Component
{
    use WithPagination;

    public $periodeAwal;

    public $periodeAkhir;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->periodeAwal = now()->startOfMonth()->format('d-m-Y');
        $this->periodeAkhir = now()->endOfMonth()->format('d-m-Y');
    }

    public function render()
    {
        return view('livewire.rekam-medis-data-table-component', [
            'statistik' => Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)
                ->orderBy('no_rawat')
                ->orderBy('no_reg')
                ->paginate(25)
        ]);
    }
}
