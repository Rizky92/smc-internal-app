<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Farmasi\Inventaris\PemesananObat;
use App\Models\Logistik\PemesananBarangNonMedis;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class AccountPayable extends Component
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

    public function getDataAccountPayableMedisProperty()
    {
        return $this->isDeferred
            ? []
            : PemesananObat::query()
                ->hutangAgingMedis($this->tglAwal, $this->tglAkhir)
                ->paginate($this->perpage, ['*'], 'page_medis');
    }

    public function getDataAccountPayableNonMedisProperty()
    {
        return $this->isDeferred
            ? []
            : PemesananBarangNonMedis::query()
                ->hutangAgingNonMedis($this->tglAwal, $this->tglAkhir)
                ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function render()
    {
        return view('livewire.keuangan.account-payable')
            ->layout(BaseLayout::class, ['title' => 'Hutang Aging (Account Payable)']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Medis' => PemesananObat::query()->hutangAgingMedis($this->tglAwal, $this->tglAkhir)->get(),
            'Non Medis' => PemesananBarangNonMedis::query()->hutangAgingNonMedis($this->tglAwal, $this->tglAkhir)->get(),
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
