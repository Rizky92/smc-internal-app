<?php

namespace App\Http\Livewire\Aplikasi;

use App\Models\Bidang;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants;

class BidangUnit extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.aplikasi.bidang-unit')
            ->layout(BaseLayout::class, ['title' => 'Bidang Unit RS']);
    }

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Collection<Bidang>
     */
    public function getBidangUnitProperty(): Collection
    {
        return Bidang::query()
            ->whereNull('parent_id')
            ->search($this->cari, ['nama'])
            ->with(['descendants' => fn (Descendants $q): Descendants => $q->depthFirst()])
            ->get();
    }

    protected function defaultValues(): void
    {
        //
    }
}
