<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\GetAssessmentElementListAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Akreditasi\Standard;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class AssessmentElementList extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected $listeners = [
        'assessment-element-saved' => '$refresh',
    ];

    public function getStandardsProperty(): array
    {
        return Standard::query()
            ->with('focusArea')
            ->orderBy('focus_area_id')
            ->orderBy('urutan')
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => "[{$s->focusArea->kode}] {$s->kode} - {$s->judul}"])
            ->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return app(GetAssessmentElementListAction::class)->execute([
            'search'      => $this->cari,
            'standard_id' => $this->searchStandard,
        ], $this->perpage);
    }

    /** @var string */
    public $searchStandard = '';

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.assessment-element-list', [
            'elements' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Elemen Penilaian']);
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
