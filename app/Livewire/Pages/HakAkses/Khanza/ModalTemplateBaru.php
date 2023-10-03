<?php

namespace App\Livewire\Pages\HakAkses\Khanza;

use App\Livewire\Concerns\DeferredModal;
use Illuminate\View\View;
use Livewire\Component;

class ModalTemplateBaru extends Component
{
    use DeferredModal;

    /** @var mixed */
    protected $listeners = [
        'khanza.show-mtb' => 'showModal',
        'khanza.hide-mtb' => 'hideModal',
        'khanza.save-mtb' => 'templateBaru',
    ];

    /** @var array */
    protected $rules = [
        'namaTemplate' => ['required', 'string', 'max:255'],
    ];

    /** @var string|null */
    public $namaTemplate = null;

    public function render(): View
    {
        return view('livewire.pages.hak-akses.khanza.modal-template-baru');
    }
}
