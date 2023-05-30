<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StokDaruratLogistik extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var bool */
    public $tampilkanSaranOrderNol;

    protected function queryString(): array
    {
        return [
            'tampilkanSaranOrderNol' => ['except' => true],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getStokDaruratLogistikProperty(): Paginator
    {
        return BarangNonMedis::query()
            ->daruratStok($this->tampilkanSaranOrderNol)
            ->search($this->cari, [
                'ipsrsbarang.kode_brng',
                'ipsrsbarang.nama_brng',
                "IFNULL(ipsrssuplier.nama_suplier, '-')",
                'ipsrsjenisbarang.nm_jenis',
                'kodesatuan.satuan',
            ])
            ->sortWithColumns($this->sortColumns, [
                'nama_supplier' => "IFNULL(ipsrssuplier.nama_suplier, '-')",
                'jenis'         => 'ipsrsjenisbarang.nm_jenis',
                'stokmin'       => DB::raw("IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0)"),
                'stokmax'       => DB::raw("IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0)"),
                'saran_order'   => DB::raw("IFNULL(IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0')"),
                'total_harga'   => DB::raw("(ipsrsbarang.harga * (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok))"),
            ], ['ipsrsbarang.nama_brng' => 'asc'])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.logistik.stok-darurat-logistik')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Barang Logistik']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tampilkanSaranOrderNol = true;
    }

    protected function dataPerSheet(): array
    {
        return [
            BarangNonMedis::query()
                ->daruratStok($this->tampilkanSaranOrderNol)
                ->get()
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
            now()->translatedFormat('d F Y'),
        ];
    }
}
