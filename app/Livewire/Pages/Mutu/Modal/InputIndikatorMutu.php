<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityIndicatorAction;
use App\Application\Quality\DTOs\QualityIndicatorData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputIndikatorMutu extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $indikatorId = null;

    /** @var int */
    public $quality_indicator_profile_id;

    /** @var string */
    public $dep_id;

    /** @var string|null */
    public $person_in_charge;

    /** @var string|null */
    public $data_source;

    /** @var string */
    public $status = 'active';

    protected $listeners = [
        'prepare'                         => 'loadIndicator',
        'input-indikator-mutu.hide-modal' => 'hideModal',
        'input-indikator-mutu.show-modal' => 'showModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function hydrate(): void
    {
        $this->emit('select2.hydrate');
    }

    public function getDepartemenProperty(): Collection
    {
        return Departemen::query()->pluck('nama', 'dep_id');
    }

    public function getProfileProperty(): Collection
    {
        return QualityIndicatorProfile::query()->pluck('title', 'id');
    }

    public function loadIndicator(?int $id = null): void
    {
        $this->resetExcept([]);
        $this->defaultValues();

        if ($id) {
            $indicator = QualityIndicator::findOrFail($id);

            $this->indikatorId = $indicator->id;
            $this->quality_indicator_profile_id = $indicator->quality_indicator_profile_id;
            $this->dep_id = $indicator->dep_id;
            $this->person_in_charge = $indicator->person_in_charge;
            $this->data_source = $indicator->data_source;
            $this->status = $indicator->status;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-indikator-mutu.show-modal');
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-indikator-mutu.hide-modal');
    }

    public function save(SaveQualityIndicatorAction $action): void
    {
        $this->validate();

        $data = QualityIndicatorData::from([
            'id'                           => $this->indikatorId,
            'quality_indicator_profile_id' => $this->quality_indicator_profile_id,
            'dep_id'                       => $this->dep_id,
            'person_in_charge'             => $this->person_in_charge,
            'data_source'                  => $this->data_source,
            'status'                       => $this->status,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Mapping Indikator Departemen berhasil disimpan.');
        $this->emit('indicator-saved');
        $this->hideModal();
    }

    protected function rules(): array
    {
        return [
            'quality_indicator_profile_id' => ['required', 'exists:mysql_smc.quality_indicator_profiles,id'],
            'dep_id'                       => ['required', 'string'],
            'status'                       => ['required', 'in:active,inactive'],
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-indikator-mutu');
    }

    protected function defaultValues(): void
    {
        $this->indikatorId = null;
        $this->quality_indicator_profile_id = null;
        $this->dep_id = '';
        $this->person_in_charge = '';
        $this->data_source = '';
        $this->status = 'active';
    }
}
