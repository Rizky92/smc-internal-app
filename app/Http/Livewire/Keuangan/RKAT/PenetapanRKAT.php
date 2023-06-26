<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class PenetapanRKAT extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var ?string */
    public $tahun;

    protected function queryString(): array
    {
        return [
            'tahun' => ['except' => now()->format('Y')],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataAnggaranBidangProperty(): Paginator
    {
        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->where('tahun', $this->tahun)
            ->paginate($this->perpage);
    }

    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2023, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.penetapan-rkat')
            ->layout(BaseLayout::class, ['title' => 'Penetapan RKAT']);
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->format('Y');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
