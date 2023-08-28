<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Models\Logistik\MinmaxStokBarangNonMedis;
use App\Models\Logistik\SupplierNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class InputMinmaxStok extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getSupplierProperty(): array
    {
        return SupplierNonMedis::pluck('nama_suplier', 'kode_suplier')->all();
    }

    public function getBarangLogistikProperty(): Paginator
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        return BarangNonMedis::query()
            ->denganMinmax()
            ->search($this->cari, [
                "ipsrsbarang.kode_brng",
                "ipsrsbarang.nama_brng",
                "IFNULL(ipsrssuplier.kode_suplier, '-')",
                "IFNULL(ipsrssuplier.nama_suplier, '-')",
                "ipsrsjenisbarang.nm_jenis",
                "kodesatuan.satuan",
            ])
            ->sortWithColumns($this->sortColumns, [
                'kode_supplier' => DB::raw("IFNULL(ipsrssuplier.kode_suplier, '-')"),
                'nama_supplier' => DB::raw("IFNULL(ipsrssuplier.nama_suplier, '-')"),
                'jenis'         => "ipsrsjenisbarang.nm_jenis",
                'stokmin'       => DB::raw("IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0)"),
                'stokmax'       => DB::raw("IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0)"),
                'saran_order'   => DB::raw("IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), IFNULL(IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0), 0)"),
                'total_harga'   => DB::raw("IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), ipsrsbarang.harga * (IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok), 0)"),
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.logistik.input-minmax-stok')
            ->layout(BaseLayout::class, ['title' => 'Stok Minmax Barang Logistik']);
    }

    public function simpan(string $kodeBarang, int $stokMin = 0, int $stokMax = 0, string $kodeSupplier = '-'): void
    {
        if (!Auth::user()->can('logistik.stok-minmax.update')) {
            $this->flashError('Anda tidak memiliki izin untuk mengupdate barang');

            return;
        }

        $kodeSupplier = $kodeSupplier !== '-' ? $kodeSupplier : null;

        tracker_start('mysql_smc');

        MinmaxStokBarangNonMedis::updateOrCreate([
            'kode_brng' => $kodeBarang,
        ], [
            'stok_min' => $stokMin,
            'stok_max' => $stokMax,
            'kode_suplier' => $kodeSupplier,
        ]);

        tracker_end('mysql_smc');

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-tersimpan');

        $this->flashSuccess('Data berhasil disimpan!');
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            BarangNonMedis::query()
                ->denganMinmax($export = true)
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
            'Minmax Stok Barang Non Medis',
            now()->translatedFormat('d F Y'),
        ];
    }
}
