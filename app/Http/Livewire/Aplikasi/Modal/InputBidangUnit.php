<?php

namespace App\Http\Livewire\Aplikasi\Modal;

use App\Models\Bidang;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class InputBidangUnit extends Component
{
    use DeferredModal, Filterable;

    /** @var int */
    public $bidangId;

    /** @var string */
    public $nama;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'bidang.show-modal' => 'showModal',
        'bidang.hide-modal' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.aplikasi.modal.input-bidang-unit');
    }

    public function prepare(int $id = -1, string $nama = ''): void
    {
        $this->bidangId = $id;
        $this->nama = $nama;
    }

    public function create(): void
    {
        if ($this->bidangId !== -1) {
            $this->update();

            return;
        }

        if (! Auth::user()->can('aplikasi.bidang-unit.create')) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        Bidang::create(['nama' => $this->nama]);

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Bidang baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (! Auth::user()->can('aplikasi.bidang-unit.update')) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        $bidang = Bidang::find($this->bidangId);

        if (! $bidang) {
            $this->dispatchBrowserEvent('data-not-found');
            $this->emit('flash.error', 'Tidak dapat menemukan data yang bisa diupdate. Silahkan coba kembali.');

            return;
        }

        $bidang->nama = $this->nama;

        $bidang->save();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data bidang berhasil diubah!');
    }

    protected function defaultValues(): void
    {
        $this->bidangId = -1;
        $this->nama = '';
    }
}
