<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class IndikatorMutu extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $depId;

    /** @var mixed */
    protected $listeners = [
        'record-saved'    => '$refresh',
        'indicator-saved' => '$refresh',
    ];

    protected function queryString(): array
    {
        return [
            'depId' => ['except' => '', 'as' => 'dep'],
        ];
    }

    public function getDepartemenProperty(): array
    {
        return Departemen::query()->pluck('nama', 'dep_id')->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return $this->query()->paginate($this->perpage);
    }

    /**
     * Tanpa pilihan departemen, layar dan export memakai departemen user yang login.
     */
    public function getDepartemenAktifProperty(): ?string
    {
        return $this->depId ?: user_departemen_id();
    }

    protected function query(): Builder
    {
        return QualityIndicator::query()
            ->with(['profile', 'departemen'])
            ->when($this->departemenAktif, fn ($q) => $q->departemen($this->departemenAktif))
            ->when($this->cari, fn ($q) => $q->search($this->cari));
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.indikator-mutu', [
            'indicators' => $this->isDeferred ? [] : $this->collection,
            'noMapping'  => ! $this->isDeferred && empty($this->departemenAktif),
        ])
            ->layout(BaseLayout::class, ['title' => 'Mapping Indikator Departemen']);
    }

    protected function defaultValues(): void
    {
        $this->depId = '';
    }

    protected function dataPerSheet(): array
    {
        return [
            'Mapping Indikator' => fn () => $this->query()
                ->get()
                ->map(fn ($indicator) => [
                    $indicator->id,
                    $indicator->profile->title ?? '-',
                    $indicator->departemen->nama ?? '-',
                    $indicator->profile->standard ?? '-',
                    $indicator->person_in_charge,
                    $indicator->status === 'active' ? 'Aktif' : 'Nonaktif',
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'ID',
            'Indikator',
            'Departemen',
            'Standar',
            'PJ',
            'Status',
        ];
    }

    protected function pageHeaders(): array
    {
        $depName = $this->departemenAktif
            ? (Departemen::find($this->departemenAktif)->nama ?? $this->departemenAktif)
            : 'SEMUA';

        return [
            'MAPPING INDIKATOR MUTU PER DEPARTEMEN',
            'DEPARTEMEN: '.strtoupper($depName),
            'Tanggal Cetak: '.now()->format('d-m-Y H:i:s'),
        ];
    }
}
