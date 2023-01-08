<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganPerPoli extends Component
{
    use WithPagination;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function getDataKunjunganResepPasienProperty()
    {
        return ResepObat::kunjunganFarmasi($this->periodeAwal, $this->periodeAkhir, $this->cari)->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Farmasi Pasien Per Poli']);
    }

    public function beginExcelExport()
    {

    }

    public function exportToExcel()
    {
        
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
