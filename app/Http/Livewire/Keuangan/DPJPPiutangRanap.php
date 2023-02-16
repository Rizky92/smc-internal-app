<?php

namespace App\Http\Livewire\Keuangan;

use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class DPJPPiutangRanap extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.keuangan.dpjp-piutang-ranap')
            ->layout(BaseLayout::class, ['title' => 'DPJP Piutang Ranap']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
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
