<?php

namespace App\Livewire\Pages\Mutu;

use App\Application\Quality\Actions\GetAllQualityIndicatorAction;
use App\Application\Quality\Actions\GetQualityIndicatorListAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Kepegawaian\Departemen;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    /** @var bool */
    public $noMapping = false;

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
        $depId = $this->depId;

        if (empty($depId)) {
            $userDepId = user_departemen_id();

            if (empty($userDepId)) {
                $this->noMapping = false;

                return app(GetQualityIndicatorListAction::class)->execute([
                    'search' => $this->cari,
                ], $this->perpage);
            }

            $this->noMapping = false;

            return app(GetQualityIndicatorListAction::class)->execute([
                'dep_ids' => [$userDepId],
                'search'  => $this->cari,
            ], $this->perpage);
        }

        $this->noMapping = false;

        return app(GetQualityIndicatorListAction::class)->execute([
            'dep_id' => $depId,
            'search' => $this->cari,
        ], $this->perpage);
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.indikator-mutu', [
            'indicators' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Mapping Indikator Departemen']);
    }

    protected function defaultValues(): void
    {
        $this->depId = '';
        $this->noMapping = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            'Mapping Indikator' => fn () => app(GetAllQualityIndicatorAction::class)
                ->execute([
                    'dep_id' => $this->depId,
                    'search' => $this->cari,
                ])
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
            'Urutan',
            'Indikator',
            'Departemen',
            'Standar',
            'PJ',
            'Status',
        ];
    }

    protected function pageHeaders(): array
    {
        $depName = $this->depId ? (Departemen::find($this->depId)->nama ?? 'SEMUA') : 'SEMUA';

        return [
            'MAPPING INDIKATOR MUTU PER DEPARTEMEN',
            'DEPARTEMEN: '.strtoupper($depName),
            'Tanggal Cetak: '.now()->format('d-m-Y H:i:s'),
        ];
    }
}
