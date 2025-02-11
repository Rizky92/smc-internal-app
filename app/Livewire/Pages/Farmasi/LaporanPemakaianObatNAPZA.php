<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatNAPZA extends Component
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

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|Paginator
     */
    public function getDataPemakaianObatNarkotikaProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_narkotika');
    }

    /**
     * @return array<empty, empty>|Paginator
     */
    public function getDataPemakaianObatPsikotropikaProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_psikotropika');
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pemakaian-obat-napza')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat NAPZA']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    protected function dataPerSheet(): array
    {
        $map = function (Obat $model): array {
            $stokAwal = round($model->stok_awal_terakhir, 2);

            if (round($model->stok_awal, 2) > 0) {
                $stokAwal = round($model->stok_awal, 2);
            }

            return [
                'kode_brng'       => $model->kode_brng,
                'nama_brng'       => $model->nama_brng,
                'golongan'        => $model->nama,
                'satuan'          => $model->satuan,
                'stok_awal'       => $stokAwal,
                'tf_masuk'        => round($model->tf_masuk, 2),
                'penerimaan_obat' => round($model->penerimaan_obat, 2),
                'piutang_masuk'   => round($model->piutang_masuk, 2),
                'hibah_obat'      => round($model->hibah_obat, 2),
                'retur_pasien'    => round($model->retur_pasien + $model->hapus_beriobat, 2),
                'total_masuk'     => $totalMasuk = round($model->tf_masuk + $model->penerimaan_obat + $model->piutang_masuk + $model->hibah_obat + $model->retur_pasien + $model->hapus_beriobat, 2),
                'pemberian_obat'  => round($model->pemberian_obat + $model->hapus_beriobat, 2),
                'penjualan_obat'  => round($model->penjualan_obat, 2),
                'piutang_keluar'  => round($model->piutang_keluar, 2),
                'tf_keluar'       => round($model->tf_keluar, 2),
                'retur_supplier'  => round($model->retur_supplier, 2),
                'total_keluar'    => $totalKeluar = round($model->pemberian_obat + $model->hapus_beriobat + $model->penjualan_obat + $model->piutang_keluar + $model->tf_keluar + $model->retur_supplier, 2),
                'stok_akhir'      => round($stokAwal + $totalMasuk - $totalKeluar, 2),
            ];
        };

        return [
            'Narkotika' => fn () => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
                ->cursor()
                ->map($map),

            'Psikotropika' => fn () => Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
                ->cursor()
                ->map($map),
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
            'Piutang Masuk',
            'Hibah Obat',
            'Retur Obat Pasien',
            'Total Masuk',
            'Pemberian Obat',
            'Penjualan Obat',
            'Piutang Keluar',
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

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
