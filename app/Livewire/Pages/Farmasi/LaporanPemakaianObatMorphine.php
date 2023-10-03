<?php

namespace App\Livewire\Pages\Farmasi;

use App\Models\Farmasi\Obat;
use App\Models\Farmasi\PemberianObat;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
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
            ->search($this->cari, [
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.alamat',
                'dokter.kd_dokter',
                'dokter.nm_dokter',
            ])
            ->paginate($this->perpage, ['*'], 'page_obat_a');
    }

    public function getDataLaporanPemakaianObatMorphine02050012Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0012')
            ->search($this->cari, [
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.alamat',
                'dokter.kd_dokter',
                'dokter.nm_dokter',
            ])
            ->paginate($this->perpage, ['*'], 'page_obat_b');
    }

    public function getDataLaporanPemakaianObatMorphine02050013Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0013')
            ->search($this->cari, [
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.alamat',
                'dokter.kd_dokter',
                'dokter.nm_dokter',
            ])
            ->paginate($this->perpage, ['*'], 'page_obat_c');
    }

    public function getDataLaporanPemakaianObatMorphine02050014Property(): Paginator
    {
        return PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, '02.05.0014')
            ->search($this->cari, [
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'pasien.alamat',
                'dokter.kd_dokter',
                'dokter.nm_dokter',
            ])
            ->paginate($this->perpage, ['*'], 'page_obat_d');
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-morphine')
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
            'No. Rawat',
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
