<?php

namespace App\Livewire\Pages\Akreditasi\Modal;

use App\Application\Akreditasi\Actions\SaveStandardAction;
use App\Application\Akreditasi\DTOs\StandardData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Akreditasi\FocusArea;
use App\Models\Akreditasi\Standard;
use Illuminate\View\View;
use Livewire\Component;

class InputStandard extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $standardId = null;

    public string $focusAreaId = '';

    public string $kode = '';

    public string $judul = '';

    public ?string $maksudTujuan = null;

    public int $urutan = 0;

    protected $listeners = [
        'prepare'                           => 'loadStandard',
        'input-standard.hide-modal'         => 'hideModal',
        'input-standard.show-modal'         => 'showModal',
    ];

    public function getFocusAreasProperty(): array
    {
        return FocusArea::query()->orderBy('urutan')->pluck('nama', 'id')->all();
    }

    public function loadStandard(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $standard = Standard::findOrFail($id);
            $this->standardId = $standard->id;
            $this->focusAreaId = (string) $standard->focus_area_id;
            $this->kode = $standard->kode;
            $this->judul = $standard->judul;
            $this->maksudTujuan = $standard->maksud_tujuan;
            $this->urutan = $standard->urutan;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-standard.show-modal');
    }

    public function save(SaveStandardAction $action): void
    {
        $data = StandardData::from([
            'id'            => $this->standardId,
            'focus_area_id' => $this->focusAreaId,
            'kode'          => $this->kode,
            'judul'         => $this->judul,
            'maksud_tujuan' => $this->maksudTujuan,
            'urutan'        => $this->urutan,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Standar berhasil disimpan.');
        $this->emit('standard-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-standard.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.akreditasi.modal.input-standard');
    }
}
