<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Farmasi\Inventaris\GudangObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class StokObatRuangan extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $perpage;

    public $kodeBangsal;

    protected $paginationTheme = 'bootstrap';

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
                'except' => '-',
                'as' => 'ruangan',
            ],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
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
        return view('livewire.keuangan.stok-obat-ruangan')
            ->layout(BaseLayout::class, ['title' => 'Stok Obat Per Ruangan']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->kodeBangsal = '-';
    }

    protected function dataPerSheet(): array
    {
        return [
            GudangObat::stokPerRuangan($this->kodeBangsal)->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Bangsal',
            'Kode',
            'Nama',
            'Satuan',
            'Stok Sekarang',
            'Harga (RP)',
            'Total Harga (RP)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Stok Obat per Ruangan',
            now()->format('d F Y'),
        ];
    }
}
