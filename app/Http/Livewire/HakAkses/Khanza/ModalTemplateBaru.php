<?php

namespace App\Http\Livewire\HakAkses\Khanza;

use App\Support\Traits\Livewire\DeferredModal;
use Livewire\Component;

class ModalTemplateBaru extends Component
{
    use DeferredModal;

    protected $listeners = [
        'khanza.show-mtb' => 'showModal',
        'khanza.hide-mtb' => 'hideModal',
        'khanza.save-mtb' => 'templateBaru',
    ];

    protected $rules = [
        'namaTemplate' => ['required', 'string', 'max:255'],
    ];

    public $namaTemplate = null;

    public function render()
    {
        return view('livewire.hak-akses.khanza.modal-template-baru');
    }

    public function templateBaru()
    {
        
    }
}
