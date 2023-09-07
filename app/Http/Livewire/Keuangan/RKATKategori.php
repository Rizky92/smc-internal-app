<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\RKAT\Anggaran;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
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
