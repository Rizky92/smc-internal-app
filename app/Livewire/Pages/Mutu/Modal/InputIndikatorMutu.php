<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityIndicatorAction;
use App\Application\Quality\DTOs\QualityIndicatorData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Bidang;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorInputType;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputIndikatorMutu extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $indikatorId = null;

    /** @var int */
    public $bidang_id;

    /** @var int */
    public $quality_indicator_category_id;

    /** @var int */
    public $sort_order = 1;

    /** @var string */
    public $title;

    /** @var string */
    public $dimension;

    /** @var string */
    public $objective;

    /** @var string */
    public $definition;

    /** @var string */
    public $inclusion;

    /** @var string */
    public $exclusion;

    /** @var "Harian"|"Mingguan"|"Bulanan"|"Tahunan" */
    public $frequency;

    /** @var int */
    public $quality_indicator_input_type_id;

    /** @var int */
    public $analysis_period;

    /** @var string */
    public $numerator;

    /** @var string */
    public $denominator;

    /** @var string */
    public $data_source;

    /** @var string */
    public $standard;

    /** @var string */
    public $person_in_charge;

    /** @var "active"|"inactive" */
    public $status = 'active';

    /** @var mixed */
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

    public function getUnitProperty(): Collection
    {
        return Bidang::query()->pluck('nama', 'id');
    }

    public function getCategoryProperty(): Collection
    {
        return QualityIndicatorCategory::query()->pluck('name', 'id');
    }

    public function getInputTypeProperty(): Collection
    {
        return QualityIndicatorInputType::query()->pluck('name', 'id');
    }

    public function getFrequencyOptionsProperty(): array
    {
        return [
            'Harian'   => 'Harian',
            'Mingguan' => 'Mingguan',
            'Bulanan'  => 'Bulanan',
            'Tahunan'  => 'Tahunan',
        ];
    }

    public function loadIndicator(?int $id = null): void
    {
        $this->resetExcept([]);
        $this->defaultValues();

        if ($id) {
            $indicator = QualityIndicator::findOrFail($id);

            $this->indikatorId = $indicator->id;
            $this->bidang_id = $indicator->bidang_id;
            $this->quality_indicator_category_id = $indicator->quality_indicator_category_id;
            $this->sort_order = $indicator->sort_order;
            $this->title = $indicator->title;
            $this->dimension = $indicator->dimension;
            $this->objective = $indicator->objective;
            $this->definition = $indicator->definition;
            $this->inclusion = $indicator->inclusion;
            $this->exclusion = $indicator->exclusion;
            $this->frequency = $indicator->frequency;
            $this->quality_indicator_input_type_id = $indicator->quality_indicator_input_type_id;
            $this->analysis_period = $indicator->analysis_period;
            $this->numerator = $indicator->numerator;
            $this->denominator = $indicator->denominator;
            $this->data_source = $indicator->data_source;
            $this->standard = $indicator->standard;
            $this->person_in_charge = $indicator->person_in_charge;
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
            'id'                              => $this->indikatorId,
            'bidang_id'                       => $this->bidang_id,
            'quality_indicator_category_id'   => $this->quality_indicator_category_id,
            'sort_order'                      => $this->sort_order,
            'title'                           => $this->title,
            'dimension'                       => $this->dimension,
            'objective'                       => $this->objective,
            'definition'                      => $this->definition,
            'inclusion'                       => $this->inclusion,
            'exclusion'                       => $this->exclusion,
            'frequency'                       => $this->frequency,
            'quality_indicator_input_type_id' => $this->quality_indicator_input_type_id,
            'analysis_period'                 => $this->analysis_period,
            'numerator'                       => $this->numerator,
            'denominator'                     => $this->denominator,
            'data_source'                     => $this->data_source,
            'standard'                        => $this->standard,
            'person_in_charge'                => $this->person_in_charge,
            'status'                          => $this->status,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Data Indikator Mutu berhasil disimpan.');
        $this->emit('indicator-saved');
        $this->hideModal();
    }

    protected function rules(): array
    {
        return [
            'bidang_id'                       => ['required', 'exists:mysql_smc.bidang,id'],
            'quality_indicator_category_id'   => ['required', 'exists:mysql_smc.quality_indicator_categories,id'],
            'sort_order'                      => ['required', 'integer', 'min:1'],
            'title'                           => ['required', 'string'],
            'dimension'                       => ['nullable', 'string'],
            'objective'                       => ['nullable', 'string'],
            'definition'                      => ['nullable', 'string'],
            'inclusion'                       => ['nullable', 'string'],
            'exclusion'                       => ['nullable', 'string'],
            'frequency'                       => ['required', 'string'],
            'quality_indicator_input_type_id' => ['nullable', 'exists:mysql_smc.quality_indicator_input_types,id'],
            'analysis_period'                 => ['nullable', 'integer'],
            'numerator'                       => ['nullable', 'string'],
            'denominator'                     => ['nullable', 'string'],
            'data_source'                     => ['nullable', 'string'],
            'standard'                        => ['required', 'string'],
            'person_in_charge'                => ['nullable', 'string'],
            'status'                          => ['required', 'in:active,inactive'],
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-indikator-mutu');
    }

    protected function defaultValues(): void
    {
        $this->indikatorId = null;
        $this->bidang_id = null;
        $this->quality_indicator_category_id = null;
        $this->sort_order = 1;
        $this->title = '';
        $this->dimension = '';
        $this->objective = '';
        $this->definition = '';
        $this->inclusion = '';
        $this->exclusion = '';
        $this->frequency = '';
        $this->quality_indicator_input_type_id = null;
        $this->analysis_period = null;
        $this->numerator = '';
        $this->denominator = '';
        $this->data_source = '';
        $this->standard = '';
        $this->person_in_charge = '';
        $this->status = 'active';
    }
}
