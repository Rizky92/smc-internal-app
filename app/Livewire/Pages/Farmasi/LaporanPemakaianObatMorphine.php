<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\Models\Farmasi\PemberianObat;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatMorphine extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var "IFA"|"AP"|"IFG"|"IFI" */
    public $bangsal;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'bangsal'  => ['as' => 'depo'],
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

    public function getDataLaporanPemakaianObatMorphine02050011Property()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $this->bangsal, '02.05.0011')
            ->search($this->cari)
            ->paginate($this->perpage, ['*'], 'page_obat_a');
    }

    public function getDataLaporanPemakaianObatMorphine02050012Property()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $this->bangsal, '02.05.0012')
            ->search($this->cari)
            ->paginate($this->perpage, ['*'], 'page_obat_b');
    }

    public function getDataLaporanPemakaianObatMorphine02050013Property()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $this->bangsal, '02.05.0013')
            ->search($this->cari)
            ->paginate($this->perpage, ['*'], 'page_obat_c');
    }

    public function getDataLaporanPemakaianObatMorphine02050014Property()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $this->bangsal, '02.05.0014')
            ->search($this->cari)
            ->paginate($this->perpage, ['*'], 'page_obat_d');
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-morphine')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat Morfin ke Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->bangsal = 'IFA';
    }

    protected function dataPerSheet(): array
    {
        // FIXME: jadikan return sesuai dengan yang lain
        return Obat::query()
            ->whereIn('kode_brng', ['02.05.0013', '02.05.0012', '02.05.0014', '02.05.0011'])
            ->pluck('nama_brng', 'kode_brng')
            ->mapWithKeys(fn (string $v, string $k): array => [
                $v => PemberianObat::query()
                    ->laporanPemakaianObatMorphine($this->tglAwal, $this->tglAkhir, $this->bangsal, $k)
                    ->get(),
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
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        $gudang = [
            'IFA' => 'Farmasi A',
            'AP'  => 'Farmasi B',
            'IFG' => 'Farmasi IGD',
            'IFI' => 'Farmasi Rawat Inap',
        ];

        return [
            'RS Samarinda Medika Citra',
            'Pemakaian Obat Morfin '.$gudang[$this->bangsal],
            $periode,
        ];
    }
}
