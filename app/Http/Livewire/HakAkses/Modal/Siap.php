<?php

namespace App\Http\Livewire\HakAkses\Modal;

use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\Filterable;
use Livewire\Component;

class Siap extends Component
{
    use Filterable, DeferredLoading;

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.hak-akses.modal.siap');
    }

    protected function defaultValues()
    {
    }
}
