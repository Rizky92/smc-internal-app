<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatNAPZA extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
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
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Narkotika' => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
                ->get()
                ->map(fn (Obat $model, $_): array => [
                    'kode_brng'       => $model->kode_brng,
                    'nama_brng'       => $model->nama_brng,
                    'golongan'        => $model->nama,
                    'satuan'          => $model->satuan,
                    'stok_awal'       => round($model->stok_awal, 2),
                    'tf_masuk'        => round($model->tf_masuk, 2),
                    'penerimaan_obat' => round($model->penerimaan_obat, 2),
                    'hibah_obat'      => round($model->hibah_obat, 2),
                    'retur_pasien'    => round($model->retur_pasien + $model->hapus_beriobat, 2),
                    'total_masuk'     => $totalMasuk = round($model->tf_masuk + $model->penerimaan_obat + $model->hibah_obat + $model->retur_pasien + $model->hapus_beriobat, 2),
                    'pemberian_obat'  => round($model->pemberian_obat + $model->hapus_beriobat, 2),
                    'penjualan_obat'  => round($model->penjualan_obat, 2),
                    'tf_keluar'       => round($model->tf_keluar, 2),
                    'retur_supplier'  => round($model->retur_supplier, 2),
                    'total_keluar'    => $totalKeluar = round($model->pemberian_obat + $model->hapus_beriobat + $model->penjualan_obat + $model->tf_keluar + $model->retur_supplier, 2),
                    'stok_akhir'      => round($model->stok_awal + $totalMasuk - $totalKeluar, 2),
                ]),
            'Psikotropika' => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
                ->get()
                ->map(fn (Obat $model, $_): array => [
                    'kode_brng'       => $model->kode_brng,
                    'nama_brng'       => $model->nama_brng,
                    'golongan'        => $model->nama,
                    'satuan'          => $model->satuan,
                    'stok_awal'       => round($model->stok_awal, 2),
                    'tf_masuk'        => round($model->tf_masuk, 2),
                    'penerimaan_obat' => round($model->penerimaan_obat, 2),
                    'hibah_obat'      => round($model->hibah_obat, 2),
                    'retur_pasien'    => round($model->retur_pasien + $model->hapus_beriobat, 2),
                    'total_masuk'     => $totalMasuk = round($model->tf_masuk + $model->penerimaan_obat + $model->hibah_obat + $model->retur_pasien + $model->hapus_beriobat, 2),
                    'pemberian_obat'  => round($model->pemberian_obat + $model->hapus_beriobat, 2),
                    'penjualan_obat'  => round($model->penjualan_obat, 2),
                    'tf_keluar'       => round($model->tf_keluar, 2),
                    'retur_supplier'  => round($model->retur_supplier, 2),
                    'total_keluar'    => $totalKeluar = round($model->pemberian_obat + $model->hapus_beriobat + $model->penjualan_obat + $model->tf_keluar + $model->retur_supplier, 2),
                    'stok_akhir'      => round($model->stok_awal + $totalMasuk - $totalKeluar, 2),
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
