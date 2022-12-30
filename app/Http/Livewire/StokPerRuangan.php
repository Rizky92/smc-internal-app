<?php

namespace App\Http\Livewire;

use App\Models\Bangsal;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class StokPerRuangan extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $kodeBangsal;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'perpage' => [
                'except' => 25,
            ],
            'kodeBangsal' => [
                'except' => '',
                'as' => 'kode_bangsal',
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->kodeBangsal = '-';
    }

    public function getStokObatPerRuanganProperty()
    {
        return GudangObat::stokPerRuangan($this->kodeBangsal, $this->cari)->paginate($this->perpage);
    }

    public function getBangsalProperty()
    {
        return GudangObat::bangsalYangAda()->pluck('nm_bangsal', 'kd_bangsal');
    }

    public function render()
    {
        return view('livewire.stok-per-ruangan')
            ->layout(BaseLayout::class, ['title' => 'Stok Per Ruangan']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_stok_per_ruangan";

        $titles = [
            'RS Samarinda Medika Citra',
            'Stok Barang per Ruangan',
            now()->format('d F Y'),
        ];

        $columnHeaders = [
            'Bangsal',
            'Kode',
            'Nama',
            'Satuan',
            'Harga',
            'Stok Sekarang',
            'Total Harga',
        ];

        $data = GudangObat::stokPerRuangan($this->kodeBangsal)->get();

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
        $this->perpage = 25;
        $this->kodeBangsal = '-';

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
