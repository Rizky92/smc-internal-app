<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StokDaruratFarmasi extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public function mount()
    {
        $this->defaultValues();
    }

    public function getStokDaruratObatProperty()
    {
        return Obat::query()
            ->daruratStok()
            ->search($this->cari, [
                'databarang.kode_brng',
                'nama_brng',
                'kodesatuan.satuan',
                'kategori_barang.nama',
                'industrifarmasi.nama_industri',
            ])
            ->sortWithColumns($this->sortColumns, [
                'satuan_kecil'     => 'kodesatuan.satuan',
                'kategori'         => 'kategori_barang.nama',
                'stok_sekarang'    => DB::raw('ifnull(round(stok_gudang.stok_di_gudang, 2), 0)'),
                'saran_order'      => DB::raw('(databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0))'),
                'harga_beli'       => DB::raw('round(databarang.h_beli)'),
                'harga_beli_total' => DB::raw('round((databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0)) * databarang.h_beli)'),
            ], ['nama_brng' => 'asc'])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.stok-darurat-farmasi')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Farmasi']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    protected function dataPerSheet(): array
    {
        return [
            Obat::daruratStok(true)->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            // 'Kode',
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok minimal',
            'Stok saat ini',
            'Saran order',
            'Supplier',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Darurat Stok Farmasi',
            now()->format('d F Y'),
        ];
    }
}
