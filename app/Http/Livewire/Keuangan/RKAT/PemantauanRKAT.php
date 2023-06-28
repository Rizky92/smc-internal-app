<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        $bidang = Bidang::all();
        $anggaran = Anggaran::all();

        $pemakaianAnggaran = PemakaianAnggaran::query()
            ->selectRaw("anggaran_bidang_id, date_format(tgl_dipakai, '%Y-%m') as bulan, sum(nominal_pemakaian)")
            ->with('anggaranBidang')
            ->whereRaw('year(tgl_dipakai) = ?', $this->tahun)
            ->groupByRaw("anggaran_bidang_id, date_format(tgl_dipakai, '%Y-%m')")
            ->get();

        return [
            
        ];
    }

    protected function columnHeaders(): array
    {
        return collect(
            carbon()
                ->setYear(intval($this->tahun))
                ->toPeriod(carbon()->endOfYear(), '1 month')
        )
            ->each
            ->translatedFormat('F')
            ->prepend('Anggaran')
            ->prepend('Bidang')
            ->push('Selisih')
            ->push('Persentase')
            ->toArray();
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Pemantauan RKAT Tahun ' . $this->tahun,
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
