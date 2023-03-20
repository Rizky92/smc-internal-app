<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Farmasi\Inventaris\GudangObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StokObatRuangan extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $kodeBangsal;

    protected function queryString()
    {
        return [
            'kodeBangsal' => ['except' => '-', 'as' => 'ruangan'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return GudangObat::query()
            ->stokPerRuangan($this->kodeBangsal)
            ->search($this->cari, [
                'bangsal.nm_bangsal',
                'gudangbarang.kode_brng',
                'databarang.nama_brng',
                'kodesatuan.satuan',
            ])
            ->sortWithColumns(
                $this->sortColumns,
                ['projeksi_harga' => DB::raw('round(databarang.h_beli * if(gudangbarang.stok < 0, 0, gudangbarang.stok))')],
                ['databarang.nama_brng' => 'asc']
            )
            ->paginate($this->perpage);
    }

    public function getBangsalProperty()
    {
        return GudangObat::bangsalYangAda()->pluck('nm_bangsal', 'kd_bangsal')->all();
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
        $this->sortColumns = [];
        $this->kodeBangsal = '-';
    }

    protected function dataPerSheet(): array
    {
        return [
            GudangObat::query()
                ->stokPerRuangan($this->kodeBangsal)
                ->sortWithColumns(
                    $this->sortColumns,
                    ['projeksi_harga' => DB::raw('round(databarang.h_beli * if(gudangbarang.stok < 0, 0, gudangbarang.stok))')],
                    ['databarang.nama_brng' => 'asc']
                )
                ->paginate($this->perpage)
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
