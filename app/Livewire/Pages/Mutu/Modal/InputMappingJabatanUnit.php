<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Bidang;
use App\Models\Kepegawaian\Jabatan;
use App\Models\Quality\IndikatorJabatanUnit;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputMappingJabatanUnit extends Component
{
    use DeferredModal;
    use FlashComponent;

    public $jabatan_id;

    /** @var array */
    public $unit_ids = [];

    protected $listeners = [
        'prepare'                                => 'resetForm',
        'input-mapping-jabatan-unit.hide-modal'  => 'hideModal',
        'input-mapping-jabatan-unit.show-modal'  => 'showModal',
    ];

    public function getJabatanOptionsProperty(): Collection
    {
        return Jabatan::query()
            ->orderBy('nm_jbtn')
            ->pluck('nm_jbtn', 'kd_jbtn');
    }

    public function getUnitOptionsProperty(): Collection
    {
        return Bidang::query()
            ->orderBy('nama')
            ->pluck('nama', 'id');
    }

    public function resetForm(): void
    {
        $this->resetExcept([]);
        $this->jabatan_id = '';
        $this->unit_ids = [];
        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-mapping-jabatan-unit.show-modal');
    }

    public function save(): void
    {
        $this->validate([
            'jabatan_id' => ['required', 'string'],
            'unit_ids'   => ['required', 'array', 'min:1'],
            'unit_ids.*' => ['required', 'integer', 'exists:mysql_smc.bidang,id'],
        ]);

        foreach ($this->unit_ids as $unitId) {
            IndikatorJabatanUnit::firstOrCreate([
                'jabatan_id' => $this->jabatan_id,
                'unit_id'    => (int) $unitId,
            ]);
        }

        $this->emit('flash.success', 'Mapping jabatan unit berhasil disimpan.');
        $this->emit('mapping-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-mapping-jabatan-unit.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-mapping-jabatan-unit');
    }
}
