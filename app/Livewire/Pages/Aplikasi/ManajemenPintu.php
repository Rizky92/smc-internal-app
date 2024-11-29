<?php

namespace App\Livewire\Pages\Aplikasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\Pintu;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class ManajemenPintu extends Component
{
    use DeferredLoading;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function getPintuProperty()
    {
        return $this->isDeferred ? [] : Pintu::with('poliklinik')
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.manajemen-pintu')
            ->layout(BaseLayout::class, ['title' => 'Manajemen Pintu']);
    }
}
