<?php

namespace App\Livewire\Pages\Logistik;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Logistik\BarangNonMedis;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class StokDaruratLogistik extends Component
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

    public function getStokDaruratLogistikProperty()
    {
        return $this->isDeferred ? [] : BarangNonMedis::query()
            ->daruratStok($this->tampilkanSaranOrderNol)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, ['ipsrsbarang.nama_brng' => 'asc'])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.logistik.stok-darurat-logistik')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Barang Logistik']);
    }

    protected function defaultValues(): void
    {
        $this->tampilkanSaranOrderNol = true;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => BarangNonMedis::query()
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
            'Darurat Stok Barang Non Medis',
            now()->translatedFormat('d F Y'),
        ];
    }
}
