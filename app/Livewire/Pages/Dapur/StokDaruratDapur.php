<?php

namespace App\Livewire\Pages\Dapur;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Dapur\BarangDapur;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class StokDaruratDapur extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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

    public function getStokDaruratDapurProperty()
    {
        return $this->isDeferred ? [] : BarangDapur::query()
            ->daruratStok($this->tampilkanSaranOrderNol)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, ['dapurbarang.nama_brng' => 'asc'])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.dapur.stok-darurat-dapur')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Barang Dapur']);
    }

    protected function defaultValues(): void
    {
        $this->tampilkanSaranOrderNol = true;
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => BarangDapur::query()
                ->daruratStok($this->tampilkanSaranOrderNol)
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
            'Darurat Stok Barang Dapur',
            now()->translatedFormat('d F Y'),
        ];
    }
}
