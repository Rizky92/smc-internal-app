<?php

namespace App\Livewire\Pages\Mutu;

use App\Application\Quality\Actions\GetQualityCategoryListAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class KategoriIndikator extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected $listeners = [
        'category-saved' => '$refresh',
    ];

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return app(GetQualityCategoryListAction::class)->execute([
            'search' => $this->cari,
        ], $this->perpage);
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.kategori-indikator', [
            'categories' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Kategori Indikator']);
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
