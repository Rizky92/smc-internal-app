<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PemberianObat;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatAntibiotik extends Component
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

    /** @var string */
    public $jenisPerawatan;

    protected function queryString(): array
    {
        return [
            'tglAwal'        => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'jenisPerawatan' => ['except' => 'semua', 'as' => 'jenis_perawatan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|Paginator
     */
    public function getDataLaporanPemakaianObatAntibiotikProperty()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->laporanPemakaianObatAntibiotik($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-antibiotik')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat Antibiotik']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->jenisPerawatan = 'semua';
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => PemberianObat::query()
                ->laporanPemakaianObatAntibiotik($this->tglAwal, $this->tglAkhir, $this->jenisPerawatan)
                ->search($this->cari)
                ->cursor()
                ->map(fn (PemberianObat $model): array => [
                    'no_rawat'       => $model->no_rawat,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'nm_pasien'      => Str::transliterate($model->nm_pasien),
                    'tgl_perawatan'  => $model->tgl_perawatan,
                    'kode_brng'      => $model->kode_brng,
                    'nama_brng'      => $model->nama_brng,
                    'jml'            => $model->jml,
                    'status_layanan' => $model->status_layanan,
                    'dokter'         => $model->dokter,
                    'spesialis'      => $model->nm_sps,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'Tanggal Pemakaian',
            'Kode Obat',
            'Nama Obat',
            'Jumlah',
            'Jenis Perawatan',
            'Dokter',
            'Spesialis',
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

        return [
            'RS Samarinda Medika Citra',
            'Laporan Pemakaian Obat Antibiotik',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
