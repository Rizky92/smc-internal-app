<?php

namespace App\Livewire\Pages\Akreditasi\Modal;

use App\Application\Akreditasi\Actions\SaveFocusAreaAction;
use App\Application\Akreditasi\DTOs\FocusAreaData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Akreditasi\FocusArea;
use Illuminate\View\View;
use Livewire\Component;

class InputFocusArea extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $focusAreaId = null;

    public string $kode = '';

    public string $nama = '';

    public ?string $deskripsi = null;

    public int $urutan = 0;

    protected $listeners = [
        'prepare'                             => 'loadFocusArea',
        'input-focus-area.hide-modal'         => 'hideModal',
        'input-focus-area.show-modal'         => 'showModal',
    ];

    public function loadFocusArea(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $focusArea = FocusArea::findOrFail($id);
            $this->focusAreaId = $focusArea->id;
            $this->kode = $focusArea->kode;
            $this->nama = $focusArea->nama;
            $this->deskripsi = $focusArea->deskripsi;
            $this->urutan = $focusArea->urutan;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-focus-area.show-modal');
    }

    public function save(SaveFocusAreaAction $action): void
    {
        $data = FocusAreaData::from([
            'id'        => $this->focusAreaId,
            'kode'      => $this->kode,
            'nama'      => $this->nama,
            'deskripsi' => $this->deskripsi,
            'urutan'    => $this->urutan,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Fokus Area berhasil disimpan.');
        $this->emit('focus-area-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-focus-area.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.modal.input-focus-area');
    }
}
