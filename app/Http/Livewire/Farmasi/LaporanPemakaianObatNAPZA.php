<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatNAPZA extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var \Carbon\Carbon */
    public $tglAwal;

    /** @var \Carbon\Carbon */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataPemakaianObatNarkotikaProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
                ->search($this->cari, [
                    'databarang.kode_brng',
                    'databarang.nama_brng',
                    'databarang.kode_golongan',
                    'golongan_barang.nama',
                    'kodesatuan.kode_sat',
                    'kodesatuan.satuan',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage, ['*'], 'page_narkotika');
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataPemakaianObatPsikotropikaProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
                ->search($this->cari, [
                    'databarang.kode_brng',
                    'databarang.nama_brng',
                    'databarang.kode_golongan',
                    'golongan_barang.nama',
                    'kodesatuan.kode_sat',
                    'kodesatuan.satuan',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage, ['*'], 'page_psikotropika');
    }

    public function render(): View
    {
        return view('livewire.farmasi.laporan-pemakaian-obat-napza')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat NAPZA']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth();
        $this->tglAkhir = now()->endOfMonth();
    }

    protected function dataPerSheet(): array
    {
        return [
            'Narkotika' => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
                ->get()
                ->map(fn (Obat $model, int $_): array => [
                    'kode_brng'       => $model->kode_brng,
                    'nama_brng'       => $model->nama_brng,
                    'golongan'        => $model->nama,
                    'satuan'          => $model->satuan,
                    'stok_awal'       => round($model->stok_awal),
                    'tf_masuk'        => round($model->tf_masuk),
                    'penerimaan_obat' => round($model->penerimaan_obat),
                    'total_masuk'     => round($model->tf_masuk + $model->penerimaan_obat),
                    'pemberian_obat'  => round($model->pemberian_obat),
                    'penjualan_obat'  => round($model->penjualan_obat),
                    'tf_keluar'       => round($model->tf_keluar),
                    'total_keluar'    => round($model->pemberian_obat + $model->penjualan_obat + $model->tf_keluar),
                    'stok_akhir'      => round(($model->stok_awal + $model->tf_masuk + $model->penerimaan_obat) - ($model->pemberian_obat + $model->penjualan_obat + $model->tf_keluar)),
                ]),
            'Psikotropika' => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
                ->get()
                ->map(fn (Obat $model, int $_): array => [
                    'kode_brng'       => $model->kode_brng,
                    'nama_brng'       => $model->nama_brng,
                    'golongan'        => $model->nama,
                    'satuan'          => $model->satuan,
                    'stok_awal'       => round($model->stok_awal),
                    'tf_masuk'        => round($model->tf_masuk),
                    'penerimaan_obat' => round($model->penerimaan_obat),
                    'hibah_obat'      => round($model->hibah_obat),
                    'retur_pasien'    => round($model->retur_pasien),
                    'total_masuk'     => round($model->tf_masuk + $model->penerimaan_obat + $model->hibah_obat + $model->retur_pasien),
                    'pemberian_obat'  => round($model->pemberian_obat),
                    'penjualan_obat'  => round($model->penjualan_obat),
                    'tf_keluar'       => round($model->tf_keluar),
                    'retur_supplier'  => round($model->retur_supplier),
                    'total_keluar'    => round($model->pemberian_obat + $model->penjualan_obat + $model->tf_keluar + $model->retur_supplier),
                    'stok_akhir'      => round(
                        ($model->stok_awal + $model->tf_masuk + $model->penerimaan_obat + $model->hibah_obat + $model->retur_pasien) -
                        ($model->pemberian_obat + $model->penjualan_obat + $model->tf_keluar + $model->retur_supplier)
                    ),
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Golongan',
            'Satuan',
            'Stok Awal',
            'Transfer Obat Masuk',
            'Penerimaan Obat',
            'Hibah Obat',
            'Retur Obat Pasien',
            'Total Masuk',
            'Pemberian Obat',
            'Penjualan Obat',
            'Transfer Obat Keluar',
            'Retur Ke Supplier',
            'Total Keluar',
            'Stok Akhir',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Pemakaian Obat NAPZA',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
