<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\ReturSupplierObat;
use App\Models\Farmasi\MutasiObat;
use App\Models\Farmasi\PemberianObat;
use App\Models\Farmasi\PenerimaanObat;
use App\Models\Farmasi\PengeluaranObat;
use App\Models\Farmasi\PenjualanObat;
use App\Models\Farmasi\ResepObat;
use App\Models\Farmasi\ReturObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanProduksiTahunan extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-produksi-tahunan')
            ->layout(BaseLayout::class, ['title' => 'Laporan Produksi Tahunan Farmasi']);
    }

    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2022, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function getKunjunganRalanProperty(): array
    {
        return $this->isDeferred ? [] : ResepObat::kunjunganPasienRalan($this->tahun);
    }

    public function getKunjunganRanapProperty(): array
    {
        return $this->isDeferred ? [] : ResepObat::kunjunganPasienRanap($this->tahun);
    }

    public function getKunjunganIGDProperty(): array
    {
        return $this->isDeferred ? [] : ResepObat::kunjunganPasienIGD($this->tahun);
    }

    public function getKunjunganWalkInProperty(): array
    {
        return $this->isDeferred ? [] : PenjualanObat::totalKunjunganWalkIn($this->tahun);
    }

    public function getKunjunganTotalProperty(): array
    {
        if ($this->isDeferred) {
            return [];
        }

        $kunjunganTotal = [];

        foreach ($this->kunjunganRalan as $key => $_) {
            $kunjunganTotal[$key] =
                $this->kunjunganRalan[$key] +
                $this->kunjunganRanap[$key] +
                $this->kunjunganIGD[$key] +
                $this->kunjunganWalkIn[$key];
        }

        return $kunjunganTotal;
    }

    public function getPendapatanObatRalanProperty(): array
    {
        return $this->isDeferred ? [] : PemberianObat::pendapatanObatRalan($this->tahun);
    }

    public function getPendapatanObatRanapProperty(): array
    {
        return $this->isDeferred ? [] : PemberianObat::pendapatanObatRanap($this->tahun);
    }

    public function getPendapatanObatIGDProperty(): array
    {
        return $this->isDeferred ? [] : PemberianObat::pendapatanObatIGD($this->tahun);
    }

    public function getPendapatanObatWalkInProperty(): array
    {
        return $this->isDeferred ? [] : PenjualanObat::totalPendapatanWalkIn($this->tahun);
    }

    public function getPendapatanAlkesFarmasiDanUnitProperty(): array
    {
        return $this->isDeferred ? [] : PemberianObat::pendapatanAlkesUnit($this->tahun);
    }

    public function getPendapatanObatTotalProperty(): array
    {
        if ($this->isDeferred) {
            return [];
        }

        $pendapatanObat = [];

        foreach ($this->pendapatanObatRalan as $key => $_) {
            $pendapatanObat[$key] =
                $this->pendapatanObatRalan[$key] +
                $this->pendapatanObatRanap[$key] +
                $this->pendapatanObatIGD[$key] +
                $this->pendapatanObatWalkIn[$key];
        }

        return $pendapatanObat;
    }

    public function getReturObatProperty(): array
    {
        return $this->isDeferred ? [] : ReturObat::totalReturObat($this->tahun);
    }

    public function getPembelianFarmasiProperty(): array
    {
        return $this->isDeferred ? [] : PenerimaanObat::totalPembelianDariFarmasi($this->tahun);
    }

    public function getReturSupplierProperty(): array
    {
        return $this->isDeferred ? [] : ReturSupplierObat::totalBarangRetur($this->tahun);
    }

    public function getTotalBersihPembelianFarmasiProperty(): array
    {
        if ($this->isDeferred) {
            return [];
        }

        $totalBersih = $this->pembelianFarmasi;

        foreach ($totalBersih as $key => $_) {
            $totalBersih[$key] -= $this->returSupplier[$key];
        }

        return $totalBersih;
    }

    public function getStokKeluarMedisProperty(): array
    {
        return $this->isDeferred ? [] : PengeluaranObat::stokPengeluaranMedisFarmasi($this->tahun);
    }

    public function getTransferOrderProperty(): array
    {
        return $this->isDeferred ? [] : MutasiObat::transferOrder($this->tahun);
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->format('Y');
    }

    /**
     * @return array[][]
     *
     * @psalm-return array{0: array{0: array, 1: array, 2: array, 3: array, 4: array, 5: array, 6: array, 7: array, 8: array, 9: array, 10: array, 11: array, 12: array, 13: array, 14: array, 15: array, 16: array}}
     */
    protected function dataPerSheet(): array
    {
        $data = [
            array_merge(['TOTAL KUNJUNGAN'], $this->kunjunganTotal),
            array_merge(['Kunjungan Rawat Jalan'], $this->kunjunganRalan),
            array_merge(['Kunjungan Rawat Inap'], $this->kunjunganRanap),
            array_merge(['Kunjungan IGD'], $this->kunjunganIGD),
            array_merge(['Kunjungan Walk In'], $this->kunjunganWalkIn),
            array_merge(['TOTAL PENDAPATAN'], $this->pendapatanObatTotal),
            array_merge(['Pendapatan Obat Rawat Jalan'], $this->pendapatanObatRalan),
            array_merge(['Pendapatan Obat Rawat Inap'], $this->pendapatanObatRanap),
            array_merge(['Pendapatan Obat IGD'], $this->pendapatanObatIGD),
            array_merge(['Pendapatan Obat Walk In'], $this->pendapatanObatWalkIn),
            array_merge(['Pendapatan Alkes Farmasi dan Unit'], $this->pendapatanAlkesFarmasiDanUnit),
            array_merge(['Retur Obat'], $this->returObat),
            array_merge(['Pembelian Farmasi'], $this->pembelianFarmasi),
            array_merge(['Retur Supplier'], $this->returSupplier),
            array_merge(['TOTAL PEMBELIAN (Pembelian Farmasi - Retur Supplier)'], $this->totalBersihPembelianFarmasi),
            array_merge(['Pemakaian BHP'], $this->stokKeluarMedis),
            array_merge(['Transfer Order'], $this->transferOrder),
        ];

        return [$data];
    }

    protected function columnHeaders(): array
    {
        return [
            'LAPORAN',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            "Laporan Produksi Farmasi Tahun {$this->tahun}",
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
