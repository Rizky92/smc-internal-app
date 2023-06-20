<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PemantauanRKAT extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var ?string */
    public $tglAwal;

    /** @var ?string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanRKATProperty()
    {
        return Bidang::query()
            ->with([
                'anggaran' => fn ($q) => $q
                    ->with(['anggaran', 'pemakaian'])
                    ->withSum('pemakaian as total_pemakaian', 'nominal_pemakaian')
            ])
            ->get();
        return AnggaranBidang::query()
            ->with('bidang', 'anggaran')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.pemantauan-rkat')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian RKAT per Bidang']);
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
