<?php

namespace App\Livewire\Pages\Aplikasi\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Models\Bidang;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class InputBidangUnit extends Component
{
    use DeferredModal;
    use Filterable;

    /** @var int */
    public $bidangId;

    /** @var int */
    public $parentId;

    /** @var string */
    public $nama;

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

    #[On('prepare')]
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

        if (user()->cannot('aplikasi.bidang-unit.create')) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        tracker_start();

        Bidang::create(['nama' => $this->nama, 'parent_id' => $this->parentId === -1 ? null : $this->parentId]);

        tracker_end();

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', 'Bidang baru berhasil ditambahkan!');
    }

    public function update(): void
    {
        if (user()->cannot('aplikasi.bidang-unit.update')) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini.');

            return;
        }

        tracker_start('mysql_smc');

        Bidang::query()
            ->where('id', $this->bidangId)
            ->update([
                'nama'      => $this->nama,
                'parent_id' => $this->parentId,
            ]);

        tracker_end('mysql_smc');

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', 'Data bidang berhasil diubah!');
    }

    public function delete(): void
    {
        if (user()->cannot('aplikasi.bidang-unit.delete')) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $bidang = Bidang::find($this->bidangId);

        if (! $bidang) {
            $this->dispatch('data-not-found');
            $this->dispatch('flash.error', 'Tidak dapat menemukan data yang bisa dihapus. Silahkan coba kembali.');

            return;
        }

        if (Bidang::whereId($this->bidangId)->hasChildren()->exists()) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Bidang terkait masih ada sub-bidang! Tidak boleh dihapus!');

            return;
        }

        tracker_start('mysql_smc');

        $bidang->delete();

        tracker_end('mysql_smc');

        $this->dispatch('data-success');
        $this->dispatch('flash.success', 'Data bidang berhasil dihapus!');
    }

    protected function defaultValues(): void
    {
        $this->parentId = -1;
        $this->bidangId = -1;
        $this->nama = '';
    }
}
