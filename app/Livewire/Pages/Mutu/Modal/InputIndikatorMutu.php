<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Kepegawaian\Departemen;
use App\Models\Kepegawaian\Pegawai;
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
    public $pic_nik;

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

    /**
     * Pegawai aktif, ditambah PIC tersimpan bila ia sudah tidak aktif agar pilihannya tidak hilang.
     */
    public function getPegawaiProperty(): Collection
    {
        return Pegawai::query()
            ->where(fn ($q) => $q->where('stts_aktif', 'AKTIF')->when($this->pic_nik, fn ($q) => $q->orWhere('nik', $this->pic_nik)))
            ->orderBy('nama')
            ->get(['nik', 'nama'])
            ->mapWithKeys(fn (Pegawai $pegawai): array => [$pegawai->nik => "{$pegawai->nama} ({$pegawai->nik})"]);
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
            $this->pic_nik = $indicator->pic_nik;
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

    public function save(): void
    {
        $this->validate();

        tracker_start('mysql_smc');

        QualityIndicator::updateOrCreate(['id' => $this->indikatorId], [
            'quality_indicator_profile_id' => $this->quality_indicator_profile_id,
            'dep_id'                       => $this->dep_id,
            'person_in_charge'             => $this->person_in_charge,
            'pic_nik'                      => filled($this->pic_nik) ? $this->pic_nik : null,
            'data_source'                  => $this->data_source,
            'status'                       => $this->status,
        ]);

        tracker_end('mysql_smc');

        $this->emit('flash.success', 'Mapping Indikator Departemen berhasil disimpan.');
        $this->emit('indicator-saved');
        $this->hideModal();
    }

    protected function rules(): array
    {
        return [
            'quality_indicator_profile_id' => ['required', 'exists:mysql_smc.quality_indicator_profiles,id'],
            'dep_id'                       => ['required', 'string'],
            'pic_nik'                      => ['nullable', 'string', 'exists:mysql_sik.pegawai,nik'],
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
        $this->pic_nik = null;
        $this->data_source = '';
        $this->status = 'active';
    }
}
