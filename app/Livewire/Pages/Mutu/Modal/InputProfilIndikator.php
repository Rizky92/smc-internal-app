<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityIndicatorProfileAction;
use App\Application\Quality\DTOs\QualityIndicatorProfileData;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorInputType;
use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class InputProfilIndikator extends Component
{
    use DeferredModal;
    use FlashComponent;

    public ?int $profileId = null;

    public $quality_indicator_category_id;

    public $quality_indicator_input_type_id;

    public $title;

    public $dimension;

    public $objective;

    public $definition;

    public $inclusion;

    public $exclusion;

    public $frequency;

    public $analysis_period;

    public $numerator;

    public $denominator;

    public $standard;

    public $rationale;

    public $indicator_type;

    public $measurement_unit;

    public $formula;

    public $data_collection_method;

    public $instrument;

    public $sample_size;

    public $sampling_method;

    public $data_presentation;

    protected $listeners = [
        'prepare'                           => 'loadProfile',
        'input-profil-indikator.hide-modal' => 'hideModal',
        'input-profil-indikator.show-modal' => 'showModal',
    ];

    public function hydrate(): void
    {
        $this->emit('select2.hydrate');
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

    public function getIndicatorTypeOptionsProperty(): array
    {
        return [
            'Proses'   => 'Proses',
            'Hasil'    => 'Hasil',
            'Struktur' => 'Struktur',
        ];
    }

    public function loadProfile(?int $id = null): void
    {
        $this->resetExcept([]);
        $this->defaultValues();

        if ($id) {
            $profile = QualityIndicatorProfile::findOrFail($id);

            $this->profileId = $profile->id;
            $this->quality_indicator_category_id = $profile->quality_indicator_category_id;
            $this->quality_indicator_input_type_id = $profile->quality_indicator_input_type_id;
            $this->title = $profile->title;
            $this->dimension = $profile->dimension;
            $this->objective = $profile->objective;
            $this->definition = $profile->definition;
            $this->inclusion = $profile->inclusion;
            $this->exclusion = $profile->exclusion;
            $this->frequency = $profile->frequency;
            $this->analysis_period = $profile->analysis_period;
            $this->numerator = $profile->numerator;
            $this->denominator = $profile->denominator;
            $this->standard = $profile->standard;
            $this->rationale = $profile->rationale;
            $this->indicator_type = $profile->indicator_type;
            $this->measurement_unit = $profile->measurement_unit;
            $this->formula = $profile->formula;
            $this->data_collection_method = $profile->data_collection_method;
            $this->instrument = $profile->instrument;
            $this->sample_size = $profile->sample_size;
            $this->sampling_method = $profile->sampling_method;
            $this->data_presentation = $profile->data_presentation;
        }

        $this->isDeferred = false;
        $this->dispatchBrowserEvent('input-profil-indikator.show-modal');
    }

    public function hideModal(): void
    {
        $this->isDeferred = true;
        $this->dispatchBrowserEvent('input-profil-indikator.hide-modal');
    }

    public function save(SaveQualityIndicatorProfileAction $action): void
    {
        $this->validate();

        $data = QualityIndicatorProfileData::from([
            'id'                              => $this->profileId,
            'quality_indicator_category_id'   => $this->quality_indicator_category_id,
            'quality_indicator_input_type_id' => $this->quality_indicator_input_type_id,
            'title'                           => $this->title,
            'dimension'                       => $this->dimension,
            'objective'                       => $this->objective,
            'definition'                      => $this->definition,
            'inclusion'                       => $this->inclusion,
            'exclusion'                       => $this->exclusion,
            'frequency'                       => $this->frequency,
            'analysis_period'                 => $this->analysis_period,
            'numerator'                       => $this->numerator,
            'denominator'                     => $this->denominator,
            'standard'                        => $this->standard,
            'rationale'                       => $this->rationale,
            'indicator_type'                  => $this->indicator_type,
            'measurement_unit'                => $this->measurement_unit,
            'formula'                         => $this->formula,
            'data_collection_method'          => $this->data_collection_method,
            'instrument'                      => $this->instrument,
            'sample_size'                     => $this->sample_size,
            'sampling_method'                 => $this->sampling_method,
            'data_presentation'               => $this->data_presentation,
        ]);

        $action->execute($data);

        $this->emit('flash.success', 'Data Profil Indikator berhasil disimpan.');
        $this->emit('profile-saved');
        $this->hideModal();
    }

    protected function rules(): array
    {
        return [
            'quality_indicator_category_id' => ['required', 'exists:mysql_smc.quality_indicator_categories,id'],
            'title'                         => ['required', 'string'],
            'frequency'                     => ['required', 'string'],
            'standard'                      => ['required', 'string'],
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-profil-indikator');
    }

    protected function defaultValues(): void
    {
        $this->profileId = null;
        $this->quality_indicator_category_id = null;
        $this->quality_indicator_input_type_id = null;
        $this->title = '';
        $this->dimension = '';
        $this->objective = '';
        $this->definition = '';
        $this->inclusion = '';
        $this->exclusion = '';
        $this->frequency = '';
        $this->analysis_period = null;
        $this->numerator = '';
        $this->denominator = '';
        $this->standard = '';
        $this->rationale = '';
        $this->indicator_type = '';
        $this->measurement_unit = '';
        $this->formula = '';
        $this->data_collection_method = '';
        $this->instrument = '';
        $this->sample_size = '';
        $this->sampling_method = '';
        $this->data_presentation = '';
    }
}
