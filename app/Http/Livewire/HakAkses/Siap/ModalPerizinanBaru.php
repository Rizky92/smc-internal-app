<?php

namespace App\Http\Livewire\HakAkses\Siap;

use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Livewire\Component;

class ModalPerizinanBaru extends Component
{
    use Filterable, LiveTable, DeferredModal;

    protected $listeners = [
        //
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.hak-akses.siap.modal-perizinan-baru');
    }

    protected function defaultValues()
    {
        //
    }
}
