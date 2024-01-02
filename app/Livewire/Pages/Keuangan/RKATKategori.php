<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\RKAT\Anggaran;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class RKATKategori extends Component
{
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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
        return view('livewire.pages.keuangan.rkat-kategori')
            ->layout(BaseLayout::class, ['title' => 'Kategori RKAT']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
