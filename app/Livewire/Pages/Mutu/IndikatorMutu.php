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
use App\Models\Bidang;
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

    /** @var int */
    public $unitId;

    /** @var mixed */
    protected $listeners = [
        'record-saved'    => '$refresh',
        'indicator-saved' => '$refresh',
    ];

    protected function queryString(): array
    {
        return [
            'unitId' => ['except' => '', 'as' => 'unit'],
        ];
    }

    public function getUnitProperty(): array
    {
        return Bidang::query()->pluck('nama', 'id')->all();
    }

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return app(GetQualityIndicatorListAction::class)->execute([
            'unit_id' => $this->unitId,
            'search'  => $this->cari,
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
            ->layout(BaseLayout::class, ['title' => 'Mapping Indikator Unit']);
    }

    protected function defaultValues(): void
    {
        $this->unitId = null;
    }

    protected function dataPerSheet(): array
    {
        return [
            'Mapping Indikator' => fn () => app(GetAllQualityIndicatorAction::class)
                ->execute([
                    'unit_id' => $this->unitId,
                    'search'  => $this->cari,
                ])
                ->map(fn ($indicator) => [
                    $indicator->id,
                    $indicator->profile->title ?? '-',
                    $indicator->unit->nama ?? '-',
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
            'Unit',
            'Standar',
            'PJ',
            'Status',
        ];
    }

    protected function pageHeaders(): array
    {
        $unitName = $this->unitId ? (Bidang::find($this->unitId)->nama ?? 'SEMUA') : 'SEMUA';

        return [
            'MAPPING INDIKATOR MUTU PER UNIT',
            'UNIT: '.strtoupper($unitName),
            'Tanggal Cetak: '.now()->format('d-m-Y H:i:s'),
        ];
    }
}
