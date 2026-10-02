<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\GetStandardByFocusAreaAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Akreditasi\FocusArea;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class StandardByFocusArea extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var int */
    public $focusAreaId;

    public function getStandardsProperty(): Collection
    {
        return collect(app(GetStandardByFocusAreaAction::class)->execute($this->focusAreaId));
    }

    public function getFocusAreaProperty(): ?FocusArea
    {
        return FocusArea::find($this->focusAreaId);
    }

    public function mount(int $focusAreaId): void
    {
        $this->focusAreaId = $focusAreaId;
        $this->defaultValues();
    }

    public function render(): View
    {
        $focusArea = $this->focusArea;

        return view('livewire.pages.akreditasi.standard-by-focus-area', [
            'standards' => $this->isDeferred ? collect() : $this->standards,
            'focusArea' => $focusArea,
        ])
            ->layout(BaseLayout::class, ['title' => 'Standar - '.($focusArea->nama ?? '')]);
    }

    protected function defaultValues(): void
    {
        //
    }
}
