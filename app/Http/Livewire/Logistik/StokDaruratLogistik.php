<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class StokDaruratLogistik extends Component
{
    use WithPagination, FlashComponent;
    
    public $cari;

    public $tampilkanSaranOrderNol;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'tampilkanSaranOrderNol' => [
                'except' => true,
            ],
            'perpage' => [
                'except' => 25,
            ],
            'page' => [
                'except' => 1,
            ],
        ];
    }

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->tampilkanSaranOrderNol = true;
        $this->perpage = 25;
    }

    public function getStokDaruratLogistikProperty()
    {
        return BarangNonMedis::daruratStok(Str::lower($this->cari), $this->tampilkanSaranOrderNol)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.logistik.stok-darurat-logistik')
            ->layout(BaseLayout::class, ['title' => 'Stok Darurat Barang Logistik']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_logistik_darurat_stok.xlsx";

        $titles = [
            'RS Samarinda Medika Citra',
            'Minmax Stok Barang Non Medis',
            now()->format('d F Y'),
        ];

        $columnHeaders = [
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

        $data = BarangNonMedis::daruratStok($this->cari, $this->tampilkanSaranOrderNol)
            ->get()
            ->toArray();

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($data);

        return $excel->export();
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->tampilkanSaranOrderNol = true;
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
