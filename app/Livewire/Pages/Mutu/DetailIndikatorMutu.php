<?php

namespace App\Livewire\Pages\Mutu;

use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;
use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicator;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class DetailIndikatorMutu extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;

    /** @var int */
    public $indicatorId;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected $listeners = [
        'record-saved' => '$refresh',
    ];

    public function mount(int $indicatorId): void
    {
        $this->indicatorId = $indicatorId;
        $this->defaultValues();
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function getIndicatorProperty(): ?QualityIndicator
    {
        return app(QualityIndicatorRepositoryInterface::class)->findById($this->indicatorId);
    }

    public function getRecordsProperty(): Collection
    {
        if ($this->isDeferred) {
            return collect();
        }

        return app(QualityIndicatorRecordRepositoryInterface::class)->getByIndicatorInRange(
            $this->indicatorId,
            $this->tglAwal,
            $this->tglAkhir
        );
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.detail-indikator-mutu', [
            'indicator' => $this->indicator,
            'records'   => $this->records,
        ])
            ->layout(BaseLayout::class, ['title' => 'Detail Indikator Mutu']);
    }
}
