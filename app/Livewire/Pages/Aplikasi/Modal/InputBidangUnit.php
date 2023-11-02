<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Models\Bidang;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class InputBidangUnit extends Component
{
    use DeferredModal, Filterable;

    /** @var int */
    public $bidangId;

    /** @var int */
    public $parentId;

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

    public function getParentBidangProperty(): Collection
    {
        return Bidang::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $this->bidangId)
            ->pluck('nama', 'id');
    }

    public function render(): View
    {
        return view('livewire.pages.aplikasi.modal.input-bidang-unit');
    }

    public function prepare(int $bidangId = -1, int $parentId = -1, string $nama = ''): void
    {
        $this->parentId = $parentId;
        $this->bidangId = $bidangId;
        $this->nama = $nama;
    }

    public function create(): void
    {
        if ($this->bidangId !== -1) {
            $this->update();

            return;
        }

        if (!Auth::user()->can('aplikasi.bidang-unit.create')) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        tracker_start();

        Bidang::create(['nama' => $this->nama, 'parent_id' => $this->parentId === -1 ? null : $this->parentId]);

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Bidang baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (!Auth::user()->can('aplikasi.bidang-unit.update')) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        $bidang = Bidang::find($this->bidangId);

        if (!$bidang) {
            $this->dispatchBrowserEvent('data-not-found');
            $this->emit('flash.error', 'Tidak dapat menemukan data yang bisa diupdate. Silahkan coba kembali.');

            return;
        }

        $bidang->nama = $this->nama;
        $bidang->parent_id = $this->parentId;

        $bidang->save();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data bidang berhasil diubah!');
    }

    protected function defaultValues(): void
    {
        $this->parentId = -1;
        $this->bidangId = -1;
        $this->nama = '';
    }
}
