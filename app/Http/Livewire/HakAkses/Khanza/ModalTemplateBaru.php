<?php

namespace App\Http\Livewire\HakAkses\Khanza;

use App\Support\Traits\Livewire\DeferredModal;
use Livewire\Component;

class ModalTemplateBaru extends Component
{
    use DeferredModal;

    public function mount()
    {
        //
    }

    public function render()
    {
        return view('livewire.hak-akses.khanza.modal-template-baru');
    }
}
