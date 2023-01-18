<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class StokDaruratLogistik extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;
    
    public $cari;

    public $perpage;

    public $tampilkanSaranOrderNol;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'tampilkanSaranOrderNol' => ['except' => true],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getStokDaruratLogistikProperty()
    {
        return BarangNonMedis::daruratStok(Str::lower($this->cari), $this->tampilkanSaranOrderNol)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.logistik.stok-darurat-logistik')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Barang Logistik']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->tampilkanSaranOrderNol = true;
    }

    protected function dataPerSheet(): array
    {
        return [
            BarangNonMedis::daruratStok('', $this->tampilkanSaranOrderNol)->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Satuan',
            'Jenis',
            'Supplier',
            'Min',
            'Max',
            'Saat ini',
            'Saran order',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Darurat Stok Barang Non Medis',
            now()->format('d F Y'),
        ];
    }
}
