<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\GetAssessmentElementByStandardAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Akreditasi\Standard;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class AssessmentElementByStandard extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public $standardId;

    public function getElementsProperty(): Collection
    {
        return collect(app(GetAssessmentElementByStandardAction::class)->execute($this->standardId));
    }

    public function getStandardProperty(): ?Standard
    {
        return Standard::with('focusArea')->find($this->standardId);
    }

    public function mount(int $standardId): void
    {
        $this->standardId = $standardId;
        $this->defaultValues();
    }

    public function render(): View
    {
        $standard = $this->standard;

        return view('livewire.pages.akreditasi.assessment-element-by-standard', [
            'elements' => $this->isDeferred ? collect() : $this->elements,
            'standard' => $standard,
        ])
            ->layout(BaseLayout::class, ['title' => 'Elemen Penilaian - '.($standard->kode ?? '')]);
    }

    protected function defaultValues(): void
    {
        //
    }
}
