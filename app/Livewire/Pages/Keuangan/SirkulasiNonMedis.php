<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Logistik\BarangNonMedis;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class SirkulasiNonMedis extends Component
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
        return $this->isDeferred ? [] : BarangNonMedis::query()
            ->sirkulasiNonMedis($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.sirkulasi-non-medis')
            ->layout(BaseLayout::class, ['title' => 'Sirkulasi Non Medis']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => BarangNonMedis::query()
                ->sirkulasiNonMedis($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->cursor()
                ->map(fn (BarangNonMedis $model): array => [
                    'kode_brng'                 => $model->kode_brng,
                    'nama_brng'                 => $model->nama_brng,
                    'kode_sat'                  => $model->kode_sat,
                    'harga'                     => $model->harga,
                    'stok_awal'                 => $model->stok_awal,
                    'nilai_stok_awal'           => $model->stok_awal + $model->harga,
                    'pengadaan'                 => $model->pengadaan,
                    'sub_total_pengadaan'       => $model->sub_total_pengadaan,
                    'penerimaan'                => $model->penerimaan,
                    'sub_total_penerimaan'      => $model->sub_total_penerimaan,
                    'stok_keluar'               => $model->stok_keluar,
                    'sub_total_keluar'          => $model->sub_total_keluar,
                    'pengambilan_utd'           => $model->pengambilan_utd,
                    'sub_total_pengambilan_utd' => $model->sub_total_pengambilan_utd,
                    'hibah'                     => $model->hibah,
                    'sub_total_hibah'           => $model->sub_total_hibah,
                    'stok_akhir'                => $model->stok_awal + $model->pengadaan + $model->penerimaan + $model->hibah - $model->stok_keluar - $model->pengambilan_utd,
                    'nilai_stok_akhir'          => ($model->stok_awal + $model->pengadaan + $model->penerimaan + $model->hibah - $model->stok_keluar - $model->pengambilan_utd) * $model->harga,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'Harga(Rp)',
            'Stok Awal',
            'Stok Awal(Rp)',
            'Pengadaan',
            'Pengadaan(Rp)',
            'Penerimaan',
            'Penerimaan(Rp)',
            'Stok Keluar',
            'Stok Keluar(Rp)',
            'Pengambilan UTD',
            'Pengambilan UTD(Rp)',
            'Hibah',
            'Hibah(Rp)',
            'Stok Akhir',
            'Stok Akhir(Rp)',
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
            'Laporan Sirkulasi Non Medis',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
