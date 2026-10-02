<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\GetStandardListAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Akreditasi\FocusArea;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class StandardList extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected $listeners = [
        'standard-saved' => '$refresh',
    ];

    public function getFocusAreasProperty(): array
    {
        return FocusArea::query()->orderBy('urutan')->pluck('nama', 'id')->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return app(GetStandardListAction::class)->execute([
            'search'        => $this->cari,
            'focus_area_id' => $this->searchFocusArea,
        ], $this->perpage);
    }

    /** @var string */
    public $searchFocusArea = '';

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.standard-list', [
            'standards' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Standar Akreditasi']);
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
