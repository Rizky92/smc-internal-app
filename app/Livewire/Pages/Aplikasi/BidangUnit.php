<?php

namespace App\Livewire\Pages\Aplikasi;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Bidang;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants;

class BidangUnit extends Component
{
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.bidang-unit')
            ->layout(BaseLayout::class, ['title' => 'Bidang Unit RS']);
    }

    /**
     * @psalm-return Collection<Bidang>
     */
    public function getBidangUnitProperty(): Collection
    {
        return Bidang::query()
            ->whereNull('parent_id')
            ->search($this->cari)
            ->with(['descendants' => fn (Descendants $q): Descendants => $q->depthFirst()])
            ->get();
    }

    protected function defaultValues(): void
    {
        //
    }
}
