<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class DashboardMod extends Component
{
    use FlashComponent;
    use Filterable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tanggal;

    protected function queryString(): array
    {
        return [
            'tanggal' => ['except' => now()->toDateString(), 'as' => 'tgl_awal'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.dashboard-mod')
            ->layout(BaseLayout::class, ['title' => 'DashboardMod']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
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
