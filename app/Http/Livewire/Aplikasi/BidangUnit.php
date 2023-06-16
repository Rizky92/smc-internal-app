<?php

namespace App\Http\Livewire\Aplikasi;

use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BidangUnit extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker, DeferredLoading;

    protected function queryString(): array
    {
        return [
            //
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.aplikasi.bidang-unit')
            ->layout(BaseLayout::class, ['title' => 'Bidang Unit RS']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
