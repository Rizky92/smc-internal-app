<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\JenisPerawatan;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifRalan extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : JenisPerawatan::query()
            ->tarifRalan()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-ralan')
            ->layout(BaseLayout::class, ['title' => 'Tarif Ralan']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
