<?php

namespace App\Livewire\Pages\Dapur;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Dapur\BarangDapur;
use App\Models\Dapur\MinmaxStokBarangDapur;
use App\Models\Dapur\SupplierDapur;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class InputMinmaxStok extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getSupplierProperty(): array
    {
        return SupplierDapur::pluck('nama_suplier', 'kode_suplier')->all();
    }

    public function getBarangDapurProperty()
    {
        return $this->isDeferred ? [] : BarangDapur::query()
            ->denganMinmax()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.dapur.input-minmax-stok')
            ->layout(BaseLayout::class, ['title' => 'Stok Minmax Barang Dapur']);
    }

    public function simpan(string $kodeBarang, int $stokMin = 0, int $stokMax = 0, string $kodeSupplier = '-'): void
    {
        if (user()->cannot('dapur.stok-minmax.update')) {
            $this->flashError('Anda tidak memiliki izin untuk mengupdate barang');

            return;
        }

        $kodeSupplier = $kodeSupplier !== '-' ? $kodeSupplier : null;

        tracker_start('mysql_smc');

        MinmaxStokBarangDapur::updateOrCreate(['kode_brng' => $kodeBarang], [
            'stok_min'     => $stokMin,
            'stok_max'     => $stokMax,
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
            fn () => BarangDapur::query()
                ->denganMinmax()
                ->cursor(),
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
            'Minmax Stok Barang Dapur',
            now()->translatedFormat('d F Y'),
        ];
    }
}
