<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Keuangan\RKAT\Anggaran;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class KategoriRKAT extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataAnggaranProperty()
    {
        return Anggaran::paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.kategori-rkat')
            ->layout(BaseLayout::class, ['title' => 'Pembuatan RKAT baru']);
    }

    protected function defaultValues(): void
    {
        
    }
}
