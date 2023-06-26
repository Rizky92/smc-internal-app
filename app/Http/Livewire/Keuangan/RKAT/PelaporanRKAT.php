<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class PelaporanRKAT extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $tahun;

    /** @var string */
    public $bidang;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'tahun'    => ['except' => now()->format('Y')],
            'bidang'   => ['except' => 'SEMUA'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPenggunaanRKATProperty(): Paginator
    {
        return PemakaianAnggaran::query()
            ->with([
                'petugas',
                'anggaranBidang',
                'anggaranBidang.anggaran',
                'anggaranBidang.bidang',
            ])
            ->paginate($this->perpage);
    }
    
    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2023, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function getDataBidangProperty(): Collection
    {
        return Bidang::pluck('nama', 'id');
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.pelaporan-rkat')
            ->layout(BaseLayout::class, ['title' => 'Pelaporan Penggunaan RKAT']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->tahun = now()->format('Y');
        $this->bidang = 'SEMUA';
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
