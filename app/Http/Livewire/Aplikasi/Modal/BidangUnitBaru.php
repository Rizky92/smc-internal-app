<?php

namespace App\Http\Livewire\Aplikasi\Modal;

use App\Models\Bidang;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class BidangUnitBaru extends Component
{
    use DeferredModal, Filterable;

    /** @var int */
    public $idBidang;

    /** @var string */
    public $namaBidang;

    /** @var mixed */
    protected $listeners = [
        //
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.aplikasi.modal.bidang-unit-baru');
    }

    public function prepare(int $id = -1): void
    {
        $this->idBidang = $id;
    }

    public function create(): void
    {
        if (! Auth::user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }
    }

    public function update(): void
    {

    }

    protected function defaultValues(): void
    {
        $this->idBidang = -1;
        $this->namaBidang = '';
    }
}
