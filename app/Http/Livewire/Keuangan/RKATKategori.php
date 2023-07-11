<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\RKAT\Anggaran;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RKATKategori extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataAnggaranProperty(): Paginator
    {
        return Anggaran::paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat-kategori')
            ->layout(BaseLayout::class, ['title' => 'Kategori RKAT']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
