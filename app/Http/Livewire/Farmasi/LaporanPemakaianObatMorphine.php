<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Models\Farmasi\PemberianObat;
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
use Illuminate\Support\Str;
use Livewire\Component;

class LaporanPemakaianObatMorphine extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
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

    public function getDataObatProperty(): Collection
    {
        return Obat::query()
            ->whereIn('kode_brng', ['02.05.0013', '02.05.0012', '02.05.0014', '02.05.0011'])
            ->pluck('nama_brng', 'kode_brng')
            ->mapWithKeys(fn (string $value, string $key): array => [Str::replace('.', '', $key) => $value]);
    }

    public function getDataLaporanPemakaianObatMorphine02050011Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0011')
            ->paginate($this->perpage, ['*'], 'page_obat_a');
    }

    public function getDataLaporanPemakaianObatMorphine02050012Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0012')
            ->paginate($this->perpage, ['*'], 'page_obat_b');
    }

    public function getDataLaporanPemakaianObatMorphine02050013Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0013')
            ->paginate($this->perpage, ['*'], 'page_obat_c');
    }

    public function getDataLaporanPemakaianObatMorphine02050014Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0014')
            ->paginate($this->perpage, ['*'], 'page_obat_d');
    }

    public function render(): View
    {
        return view('livewire.farmasi.laporan-pemakaian-obat-morphine')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat Morfin ke Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return Obat::query()
            ->whereIn('kode_brng', ['02.05.0013', '02.05.0012', '02.05.0014', '02.05.0011'])
            ->pluck('nama_brng', 'kode_brng')
            ->mapWithKeys(fn (string $v, string $k): array => [
                $v => PemberianObat::query()
                    ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $k)
                    ->get()
            ])
            ->all();
    }

    protected function columnHeaders(): array
    {
        return [
            'No. RM',
            'Nama Pasien',
            'Alamat Pasien',
            'Tgl. Diberikan',
            'Jumlah',
            'Nama Dokter',
            'Alamat Dokter',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
