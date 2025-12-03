<?php

namespace App\Livewire\Pages\Antrean;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\Pintu;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class MasterPintu extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function getPintuProperty()
    {
        return $this->isDeferred ? [] : Pintu::query()
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.master-pintu')
            ->layout(BaseLayout::class, ['title' => 'Master Pintu']);
    }

    protected function defaultValues(): void
    {
        //
    }
}
