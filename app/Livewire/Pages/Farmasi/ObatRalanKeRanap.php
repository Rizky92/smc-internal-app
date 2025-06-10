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
use Illuminate\View\View;
use Livewire\Component;

class ObatRalanKeRanap extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

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

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : PemberianObat::query()
            ->ObatRalanKeRanap($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.obat-ralan-ke-ranap')
            ->layout(BaseLayout::class, ['title' => 'Obat Rawat Jalan ke Rawat Inap']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn() => PemberianObat::query()
                ->ObatRalanKeRanap($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->map(fn (PemberianObat $model): array => [
                    'no_rawat'      => $model->no_rawat,
                    'no_rkm_medis'  => $model->no_rkm_medis,
                    'nm_pasien'     => $model->nm_pasien,
                    'png_jawab'     => $model->png_jawab,
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam'           => $model->jam,
                    'kode_brng'     => $model->kode_brng,
                    'nama_brng'     => $model->nama_brng,
                    'biaya_obat'    => $model->biaya_obat,
                    'jml'           => $model->jml,
                    'total'         => $model->total,
                ])
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'Jenis Bayar',
            'Tgl. Pemberian Obat',
            'Jam',
            'Kode Barang',
            'Nama Barang',
            'Harga',
            'Jumlah',
            'Total'
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
            'Laporan Obat Rawat Jalan ke Rawat Inap',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
