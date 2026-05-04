<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityInputTypeAction;
use App\Application\Quality\DTOs\QualityInputTypeData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicatorInputType;
use Illuminate\View\View;
use Livewire\Component;

class InputTipeInputIndikator extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $inputTypeId = null;

    /** @var string */
    public $name;

    /** @var mixed */
    protected $listeners = [
        'prepare'                               => 'loadInputType',
        'input-tipe-input-indikator.hide-modal' => 'hideModal',
        'input-tipe-input-indikator.show-modal' => 'showModal',
    ];

    public function loadInputType(?int $id = null): void
    {
        $this->resetExcept([]);

        if ($id) {
            $inputType = QualityIndicatorInputType::findOrFail($id);
            $this->inputTypeId = $inputType->id;
            $this->name = $inputType->name;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-tipe-input-indikator.show-modal');
    }

    public function save(SaveQualityInputTypeAction $action): void
    {
        $data = QualityInputTypeData::from([
            'id'   => $this->inputTypeId,
            'name' => $this->name,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Tipe Input Indikator berhasil disimpan.');
        $this->emit('input-type-saved');
        $this->hideModal();
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-tipe-input-indikator.hide-modal');
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-tipe-input-indikator');
    }
}
