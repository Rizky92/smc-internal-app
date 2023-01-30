<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\NotaSelesai;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPenyelesaianBillingPerPetugas extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getBillingYangDiselesaikanProperty()
    {
        return NotaSelesai::query()
            ->billingYangDiselesaikan()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.laporan-penyelesaian-billing-per-petugas')
            ->layout(BaseLayout::class, ['title' => 'Laporan Penyelesaian Billing Pasien per Petugas']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->format('Y-m-d');
        $this->periodeAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
