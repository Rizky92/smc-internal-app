<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Perawatan\RawatInap;
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

    public $status;

    public $jenisBayar;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'status' => ['except' => 'Belum Lunas'],
            'jenisBayar' => ['except' => '', 'as' => 'kd_pj'],
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getPiutangRanapProperty()
    {
        return !$this->isReadyToLoad
            ? []
            : RawatInap::query()
                ->piutangRanap($this->periodeAwal, $this->periodeAkhir, $this->status, $this->jenisBayar)
                ->with([
                    'nota',
                    'dpjpRanap',
                    'billing' => fn ($q) => $q->totalBillingan(),
                ])
                ->withSum('cicilanPiutang as dibayar', 'besar_cicilan')
                ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.dpjp-piutang-ranap')
            ->layout(BaseLayout::class, ['title' => 'DPJP Piutang Ranap']);
    }

    protected function defaultValues()
    {
        $this->status = 'Belum Lunas';
        $this->jenisBayar = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function mapData()
    {
        if (!$this->isReadyToLoad) {
            $this->flashError('Belum bisa mengekspor, silahkan tunggu beberapa saat.');

            return;
        }

        $this->piutangRanap->map(function (RawatInap $ranap) {
            $billing = $ranap->getAttribute('billing')->sum('total');
        });
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
