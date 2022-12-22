<?php

namespace App\Http\Livewire\RekamMedis;

use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class DemografiPasien extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public function mount()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.rekam-medis.demografi-pasien')
            ->layout(BaseLayout::class, ['title' => 'Demografi Pasien']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }
}
