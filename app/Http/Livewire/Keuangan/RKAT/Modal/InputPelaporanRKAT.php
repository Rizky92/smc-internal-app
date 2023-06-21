<?php

namespace App\Http\Livewire\Keuangan\RKAT\Modal;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class InputPelaporanRKAT extends Component
{
    use FlashComponent, Filterable, DeferredModal;

    /** @var bool */
    public $isUpdating;

    /** @var int */
    public $anggaranBidangId;

    /** @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection */
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

    public function prepare(int $anggaranBidangId = -1): void
    {
        if ($anggaranBidangId === -1) {
            return;
        }

        $this->anggaranBidangId = $anggaranBidangId;

        /** @var \App\Models\Keuangan\RKAT\AnggaranBidang */
        $anggaranBidang = AnggaranBidang::query()
            ->with('pemakaian')
            ->find($anggaranBidangId);

        $this->data = $anggaranBidang->pemakaian;
    }

    public function create(): void
    {
        // TODO: Ganti setting dengan proses setting aplikasi
        $settings = false;

        /** 
         * @psalm-suppress TypeDoesNotContainType
         * @psalm-suppress RedundantCondition
         */
        if (! Auth::user()->can('keuangan.rkat.pelaporan-rkat.input-rkat') || ! $settings) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        if ($this->data->isAssoc()) {
            $this->emit('flash.error', 'Terjadi error! Cek kembali data yang diinputkan.');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        $this->data->each(function (array $pemakaian) {
            PemakaianAnggaran::create([
                'deskripsi' => $pemakaian['deskripsi'],
                'nominal_pemakaian' => $pemakaian['nominal'],
                'tgl_dipakai' => $pemakaian['tgl_dipakai'],
                'anggaran_bidang_id' => $this->anggaranBidangId,
                'user_id' => Auth::user()->nik,
            ]);
        });
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
        $this->isUpdating = false;
    }

    public function updating(): void
    {
        $this->isUpdating = true;
    }

    public function creating(): void
    {
        $this->isUpdating = false;
    }
}
