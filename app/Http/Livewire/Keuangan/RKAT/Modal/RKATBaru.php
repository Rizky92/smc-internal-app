<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\View\View;
use Livewire\Component;

class RKATBaru extends Component
{
    use DeferredModal, Filterable;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.modal.rkat-baru');
    }

    protected function defaultValues(): void
    {
        //
    }
}
