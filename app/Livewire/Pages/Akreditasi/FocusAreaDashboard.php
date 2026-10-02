<?php

namespace App\Livewire\Pages\Akreditasi;

use App\Application\Akreditasi\Actions\GetFocusAreaDashboardAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class FocusAreaDashboard extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function getDashboardDataProperty(): Collection
    {
        return collect(app(GetFocusAreaDashboardAction::class)->execute());
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.focus-area-dashboard', [
            'focusAreas' => $this->isDeferred ? collect() : $this->dashboardData,
        ])
            ->layout(BaseLayout::class, ['title' => 'Asesmen Mandiri Akreditasi']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
