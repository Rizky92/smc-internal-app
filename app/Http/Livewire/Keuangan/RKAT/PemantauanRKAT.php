<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Bidang;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;

class PemantauanRKAT extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
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

    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2023, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Bidang>
     */
    public function getDataLaporanRKATProperty(): Collection
    {
        return Bidang::query()
            ->with([
                'anggaran' => fn (HasMany $q) => $q
                    ->with(['anggaran', 'pemakaian'])
                    ->withSum('pemakaian as total_pemakaian', 'nominal_pemakaian')
            ])
            ->get();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.pemantauan-rkat')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian RKAT per Bidang']);
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
