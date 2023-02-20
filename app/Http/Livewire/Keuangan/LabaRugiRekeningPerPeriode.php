<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Rekening;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LabaRugiRekeningPerPeriode extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    public $tahun;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'tahun' => ['except' => now()->format('Y')],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getLabaRugiPerRekeningProperty()
    {
        if ($this->isDeferred) {
            return collect(['D' => [], 'K' => []]);
        }

        return $this->isDeferred
            ? collect(['D' => [], 'K' => []])
            : Rekening::query()
                ->perhitunganLabaRugiTahunan($this->tahun)
                ->search($this->cari, [
                    'rekening.kd_rek',
                    'rekening.nm_rek',
                    'rekening.tipe',
                    'rekening.balance',
                    'rekeningtahun.thn',
                ])
                ->sortWithColumns($this->sortColumns, [
                    'saldo_awal'  => DB::raw('ifnull(rekeningtahun.saldo_awal, 0)'),
                    'debet'       => DB::raw('round(sum(detailjurnal.debet), 2)'),
                    'kredit'      => DB::raw('round(sum(detailjurnal.kredit), 2)'),
                    'saldo_akhir' => DB::raw("case 
                        when upper(rekening.balance) = 'K'  then round((sum(detailjurnal.kredit) - sum(detailjurnal.debet)) + rekeningtahun.saldo_awal, 2)
                        when upper(rekening.balance) = 'D'  then round((sum(detailjurnal.debet) - sum(detailjurnal.kredit)) + rekeningtahun.saldo_awal, 2)
                    end"),
                ], [
                    'thn' => 'asc',
                    'kd_rek' => 'asc',
                ])
                ->get()
                ->mapToGroups(fn ($rekening) => [$rekening->balance => $rekening]);
    }

    public function getDataTahunProperty()
    {
        return collect(range((int) now()->format('Y'), 2022, -1))
            ->mapWithKeys(function ($value, $key) {
                return [$value => $value];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.keuangan.laba-rugi-rekening-per-periode')
            ->layout(BaseLayout::class, ['title' => 'Laporan Laba Rugi']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
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
            'Tahun',
            'Kode Akun',
            'Nama AKun',
            'Tipe',
            'Saldo Awal',
            'Total Debet',
            'Total Kredit',
            'Saldo Akhir',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
