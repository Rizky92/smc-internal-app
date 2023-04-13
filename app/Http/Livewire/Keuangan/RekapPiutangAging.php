<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class RekapPiutangAging extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataRekapPiutangAgingProperty()
    {
        return PenagihanPiutangDetail::query()
            ->tagihanPiutangAging($this->tglAwal, $this->tglAkhir)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.rekap-piutang-aging')
            ->layout(BaseLayout::class, ['title' => 'Rekap Piutang Aging']);
    }

    protected function defaultValues()
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
