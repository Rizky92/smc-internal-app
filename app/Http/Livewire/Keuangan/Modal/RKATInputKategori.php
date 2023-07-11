<?php

namespace App\Http\Livewire\Keuangan\Modal;

use App\Models\Keuangan\RKAT\Anggaran;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class RKATInputKategori extends Component
{
    use DeferredModal, Filterable;

    /** @var int */
    public $anggaranId;

    /** @var string */
    public $nama;

    /** @var string */
    public $deskripsi;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'kategori-rkat.show-modal' => 'showModal',
        'kategori-rkat.hide-modal' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.keuangan.modal.rkat-input-kategori');
    }

    public function prepare(int $id = -1, string $nama = '', string $deskripsi = ''): void
    {
        $this->anggaranId = $id;
        $this->nama = $nama;
        $this->deskripsi = $deskripsi;
    }

    public function create(): void
    {
        if ($this->anggaranId !== -1) {
            $this->update();

            return;
        }

        if (! Auth::user()->can('keuangan.rkat.kategori-rkat.create')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('data-denied');

            return;
        }

        tracker_start();

        Anggaran::create([
            'nama' => $this->nama,
            'deskripsi' => empty($this->deskripsi) ? null : $this->deskripsi,
        ]);

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Anggaran baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (! Auth::user()->can('keuangan.rkat.kategori-rkat.update')) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        $anggaran = Anggaran::find($this->anggaranId);

        if (! $anggaran) {
            $this->dispatchBrowserEvent('data-not-found');
            $this->emit('flash.error', 'Tidak dapat menemukan data yang bisa diupdate. Silahkan coba kembali.');

            return;
        }

        tracker_start();

        $anggaran->nama = $this->nama;
        $anggaran->deskripsi = $this->deskripsi;

        $anggaran->save();

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', 'Data anggaran berhasil diubah!');
    }

    public function isUpdating(): bool
    {
        return $this->anggaranId !== -1;
    }

    protected function defaultValues(): void
    {
        $this->anggaranId = -1;
        $this->nama = '';
    }
}
