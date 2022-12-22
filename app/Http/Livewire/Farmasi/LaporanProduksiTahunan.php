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
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Vtiful\Kernel\Excel;

class LaporanProduksiTahunan extends Component
{
    use FlashComponent;

    protected $listeners = [
        'beginExcelExport',
    ];

    public function getKunjunganRalanProperty()
    {
        return ResepObat::kunjunganPasienRalan();
    }

    public function getKunjunganRanapProperty()
    {
        return ResepObat::kunjunganPasienRanap();
    }

    public function getKunjunganIGDProperty()
    {
        return ResepObat::kunjunganPasienIGD();
    }

    public function getKunjunganWalkInProperty()
    {
        return PenjualanWalkInObat::totalKunjunganWalkIn();
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
        return PemberianObat::pendapatanObatRalan();
    }

    public function getPendapatanObatRanapProperty()
    {
        return PemberianObat::pendapatanObatRanap();
    }

    public function getPendapatanObatIGDProperty()
    {
        return PemberianObat::pendapatanObatIGD();
    }

    public function getPendapatanObatWalkInProperty()
    {
        return PenjualanWalkInObat::totalPendapatanWalkIn();
    }

    public function getPendapatanAlkesFarmasiDanUnitProperty()
    {
        return ResepObat::pendapatanAlkesFarmasiDanUnit();
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
        return ReturPenjualanObat::totalReturObat();
    }

    public function getPembelianFarmasiProperty()
    {
        return PemesananObat::totalPembelianDariFarmasi();
    }

    public function getReturSupplierProperty()
    {
        return ReturSupplierObat::totalBarangRetur();
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
        return PengeluaranStokObat::stokPengeluaranMedisFarmasi();
    }

    public function getMutasiObatDariFarmasiProperty()
    {
        return MutasiObat::mutasiObatDariFarmasi();
    }

    public function render()
    {
        return view('livewire.farmasi.laporan-produksi-tahunan')
            ->layout(BaseLayout::class, ['title' => 'Laporan Produksi Farmasi Tahun ' . now()->format('Y')]);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_laporan_produksi.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Laporan Produksi Farmasi Tahun ' . now()->format('Y');
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'Laporan',
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
            array_merge(['Transfer Order'], $this->mutasiObatDariFarmasi),
        ];

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:M1', $row1)
            ->mergeCells('A2:M2', $row2)
            ->mergeCells('A3:M3', $row3)

            // column header
            ->insertText(3, 0, $columnHeaders[0])
            ->insertText(3, 1, $columnHeaders[1])
            ->insertText(3, 2, $columnHeaders[2])
            ->insertText(3, 3, $columnHeaders[3])
            ->insertText(3, 4, $columnHeaders[4])
            ->insertText(3, 5, $columnHeaders[5])
            ->insertText(3, 6, $columnHeaders[6])
            ->insertText(3, 7, $columnHeaders[7])
            ->insertText(3, 8, $columnHeaders[8])
            ->insertText(3, 9, $columnHeaders[9])
            ->insertText(3, 10, $columnHeaders[10])
            ->insertText(3, 11, $columnHeaders[11])
            ->insertText(3, 12, $columnHeaders[12])
            ->insertText(4, 0, '')

            // insert data
            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }
}
