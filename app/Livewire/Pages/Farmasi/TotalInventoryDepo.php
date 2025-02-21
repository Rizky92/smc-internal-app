<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class TotalInventoryDepo extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $kodeBangsal;

    protected function queryString(): array
    {
        return [
            'kodeBangsal' => ['except' => '-', 'as' => 'ruangan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty ()
    {
        return $this->isDeferred ? [] : GudangObat::query()
            ->totalInventoryDepo($this->kodeBangsal)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function getBangsalProperty(): Collection
    {
        return GudangObat::query()
            ->bangsalYangAda()
            ->pluck('nm_bangsal', 'kd_bangsal');
    }


    public function render(): View
    {
        return view('livewire.pages.farmasi.total-inventory-depo')
            ->layout(BaseLayout::class, ['title' => 'Total Inventory Depo']);
    }

    protected function defaultValues(): void
    {
        $this->kodeBangsal = '-';
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => GudangObat::query()
                ->totalInventoryDepo($this->kodeBangsal)
                ->sortWithColumns($this->sortColumns)
                ->cursor()
                ->map(fn(GudangObat $model): array => [
                    'nm_bangsal'        => $model->nm_bangsal,
                    'kategori'          => $model->kategori,
                    'jumlah_kategori'   => $model->jumlah_kategori,
                    'total_harga'       => round(floatval($model->total_harga), 2)
                ])
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Bangsal',
            'Kategori',
            'Jumlah Kategori',
            'Total'
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Total Inventory Depo',
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
