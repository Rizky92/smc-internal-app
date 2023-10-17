<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTrialBalance extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<never, never>|\Illuminate\Support\Collection<string, float>
     */
    public function getSaldoRekeningBulanSebelumnyaProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
                ->saldoAwalBulanSebelumnya($this->tglAwal)
                ->pluck('saldo_awal', 'kd_rek');
    }

    /**
     * @return array<never, never>|\Illuminate\Database\Eloquent\Collection<\App\Models\Keuangan\Jurnal\Jurnal>
     */
    public function getDataTrialBalancePerTanggalProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
                ->trialBalancePerTanggal($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-trial-balance')
            ->layout(BaseLayout::class, ['title' => 'LaporanTrialBalance']);
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
