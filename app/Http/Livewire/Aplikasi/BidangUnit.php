<?php

namespace App\Http\Livewire\Aplikasi;

use App\Models\Bidang;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Livewire\Component;

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
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getBidangUnitProperty()
    {
        return Bidang::query()
            ->whereNull('parent_id')
            ->with(['descendants' => fn ($q) => $q->depthFirst()])
            ->get();
    }

    protected function defaultValues(): void
    {
        //
    }
}
