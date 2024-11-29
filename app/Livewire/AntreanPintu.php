<?php

namespace App\Livewire;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Aplikasi\Pintu;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class AntreanPintu extends Component
{
    public function getPintuProperty()
    {
        return Pintu::all();
    }

    public function render(): View
    {
        return view('livewire.antrean-pintu');
    }
}
