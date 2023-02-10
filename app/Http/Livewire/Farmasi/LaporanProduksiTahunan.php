<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\MutasiObat;
use App\Models\Farmasi\PemberianObat;
use App\Models\Farmasi\PengeluaranStokObat;
use App\Models\Farmasi\PenjualanWalkInObat;
use App\Models\Farmasi\ResepObat;
use App\Models\Farmasi\ReturPenjualanObat;
use App\Models\Farmasi\Inventaris\PemesananObat;
use App\Models\Farmasi\Inventaris\ReturSupplierObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class LaporanProduksiTahunan extends Component
{
    use FlashComponent, Filterable, ExcelExportable, MenuTracker;

    public $tahun;

    protected function queryString()
    {
        return [
            'tahun' => ['except' => now()->format('Y')],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.farmasi.laporan-produksi-tahunan')
            ->layout(BaseLayout::class, ['title' => 'Laporan Produksi Tahunan Farmasi']);
    }

    public function getDataTahunProperty()
    {
        return collect(range((int) now()->format('Y'), 2022, -1))
            ->mapWithKeys(function ($value, $key) {
                return [$value => $value];
            })
            ->toArray();
    }

    public function getKunjunganRalanProperty()
    {
        return ResepObat::kunjunganPasienRalan($this->tahun);
    }

    public function getKunjunganRanapProperty()
    {
        return ResepObat::kunjunganPasienRanap($this->tahun);
    }

    public function getKunjunganIGDProperty()
    {
        return ResepObat::kunjunganPasienIGD($this->tahun);
    }

    public function getKunjunganWalkInProperty()
    {
        return PenjualanWalkInObat::totalKunjunganWalkIn($this->tahun);
    }

    public function getKunjunganTotalProperty()
    {
        $kunjunganTotal = [];

        foreach ($this->kunjunganRalan as $key => $data) {
            $kunjunganTotal[$key] = $this->kunjunganRalan[$key] + $this->kunjunganRanap[$key] + $this->kunjunganIGD[$key] + $this->kunjunganWalkIn[$key];
        }

        return $kunjunganTotal;
    }

    public function getPendapatanObatRalanProperty()
    {
        return PemberianObat::pendapatanObatRalan($this->tahun);
    }

    public function getPendapatanObatRanapProperty()
    {
        return PemberianObat::pendapatanObatRanap($this->tahun);
    }

    public function getPendapatanObatIGDProperty()
    {
        return PemberianObat::pendapatanObatIGD($this->tahun);
    }

    public function getPendapatanObatWalkInProperty()
    {
        return PenjualanWalkInObat::totalPendapatanWalkIn($this->tahun);
    }

    public function getPendapatanAlkesFarmasiDanUnitProperty()
    {
        return PemberianObat::pendapatanAlkesUnit($this->tahun);
    }

    public function getPendapatanObatTotalProperty()
    {
        $pendapatanObat = [];

        foreach ($this->pendapatanObatRalan as $key => $data) {
            $pendapatanObat[$key] = $this->pendapatanObatRalan[$key] + $this->pendapatanObatRanap[$key] + $this->pendapatanObatIGD[$key] + $this->pendapatanObatWalkIn[$key];
        }

        return $pendapatanObat;
    }

    public function getReturObatProperty()
    {
        return ReturPenjualanObat::totalReturObat($this->tahun);
    }

    public function getPembelianFarmasiProperty()
    {
        return PemesananObat::totalPembelianDariFarmasi($this->tahun);
    }

    public function getReturSupplierProperty()
    {
        return ReturSupplierObat::totalBarangRetur($this->tahun);
    }

    public function getTotalBersihPembelianFarmasiProperty()
    {
        $totalBersih = $this->pembelianFarmasi;

        foreach ($totalBersih as $key => $data) {
            $totalBersih[$key] -= $this->returSupplier[$key];
        }

        return $totalBersih;
    }

    public function getStokKeluarMedisProperty()
    {
        return PengeluaranStokObat::stokPengeluaranMedisFarmasi($this->tahun);
    }

    public function getTransferOrderProperty()
    {
        return MutasiObat::transferOrder($this->tahun);
    }

    protected function defaultValues()
    {
        $this->tahun = now()->format('Y');
    }

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
            now()->format('d F Y'),
        ];
    }
}
