<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @template TPemakaian of \App\Models\Keuangan\RKAT\PemakaianAnggaran
 */
class InputPelaporanRKAT extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var int */
    public $anggaranBidangId;

    /** @var \Illuminate\Database\Eloquent\Collection<TPemakaian>|\Illuminate\Support\Collection<array-key, TPemakaian> */
    public $data;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'pelaporan-rkat.hide-modal' => 'hideModal',
        'pelaporan-rkat.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.modal.input-pelaporan-rkat');
    }

    public function prepare(int $anggaranBidangId = -1)
    {
        if ($anggaranBidangId === -1) {
            return;
        }

        $this->anggaranBidangId = $anggaranBidangId;

        /**
         * @var \App\Models\Keuangan\RKAT\AnggaranBidang
         */
        $anggaranBidang = AnggaranBidang::query()
            ->with('pemakaian')
            ->find($anggaranBidangId);

        $this->data = $anggaranBidang->pemakaian;
    }

    public function create()
    {

    }

    public function update()
    {

    }

    public function reorder(int $id, int $position)
    {

    }

    protected function defaultValues(): void
    {
        $this->anggaranBidangId = -1;
        $this->data = collect();
    }
}
