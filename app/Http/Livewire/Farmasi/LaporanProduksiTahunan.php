<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\PemesananBarang;
use App\Models\Farmasi\PengeluaranObat;
use App\Models\Farmasi\PenjualanWalkIn;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Models\Farmasi\ResepObat;
use App\Models\Farmasi\ReturJual;
use App\Models\Farmasi\ReturSupplier;
use App\Models\Perawatan\Registrasi;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Vtiful\Kernel\Excel;

class LaporanProduksiTahunan extends Component
{
    protected $listeners = [
        'beginExcelExport',
        'clearFilters',
        'clearFiltersAndHardRefresh',
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
        $pasienWalkInDariRalan = ResepObat::kunjunganPasienWalkIn();
        $pasienWalkInKeFarmasi = PenjualanWalkIn::totalKunjunganWalkIn();

        foreach ($pasienWalkInDariRalan as $key => $data) {
            $pasienWalkInDariRalan[$key] += $pasienWalkInKeFarmasi[$key];
        }

        return $pasienWalkInDariRalan;
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
        $resepRegular = ResepObat::pendapatanObatRalan();
        $resepRacikan = ResepDokterRacikan::pendapatanRacikanObatRalan();

        foreach ($resepRegular as $key => $data) {
            $resepRegular[$key] += $resepRacikan[$key];
        }

        return $resepRegular;
    }

    public function getPendapatanObatRanapProperty()
    {
        $resepRegular = ResepObat::pendapatanObatRanap();
        $resepRacikan = ResepDokterRacikan::pendapatanRacikanObatRanap();

        foreach ($resepRegular as $key => $data) {
            $resepRegular[$key] += $resepRacikan[$key];
        }

        return $resepRegular;
    }

    public function getPendapatanObatIGDProperty()
    {
        $resepRegular = ResepObat::pendapatanObatIGD();
        $resepRacikan = ResepDokterRacikan::pendapatanRacikanObatIGD();

        foreach ($resepRegular as $key => $data) {
            $resepRegular[$key] += $resepRacikan[$key];
        }

        return $resepRegular;
    }

    public function getPendapatanObatWalkInProperty()
    {
        $resepRegular = ResepObat::pendapatanObatWalkIn();
        $resepRacikan = ResepDokterRacikan::pendapatanRacikanObatWalkIn();

        foreach ($resepRegular as $key => $data) {
            $resepRegular[$key] += $resepRacikan[$key];
        }

        return $resepRegular;
    }

    public function getPendapatanObatTotalProperty()
    {
        $pendapatanObat = [];

        foreach ($this->pendapatanObatRalan as $key => $data) {
            $pendapatanObat[$key] = $this->pendapatanObatRalan[$key] + $this->pendapatanObatRanap[$key] + $this->pendapatanObatIGD[$key] + $this->pendapatanObatWalkIn[$key];
        }

        return $pendapatanObat;
    }

    public function getTotalReturObatProperty()
    {
        return ReturJual::totalReturObat();
    }

    public function getTotalPembelianFarmasiProperty()
    {
        return PemesananBarang::totalPembelianDariFarmasi();
    }

    public function getTotalReturObatKeSupplierProperty()
    {
        return ReturSupplier::totalBarangRetur();
    }

    public function getTotalBersihPembelianFarmasiProperty()
    {
        $totalBersih = $this->totalPembelianFarmasi;

        foreach ($totalBersih as $key => $data) {
            $totalBersih[$key] -= $this->totalReturObatKeSupplier[$key];
        }

        return $totalBersih;
    }

    public function getStokKeluarMedisProperty()
    {
        return PengeluaranObat::stokPengeluaranMedisFarmasi();
    }

    public function render()
    {
        return view('livewire.farmasi.laporan-produksi-tahunan')
            ->extends('layouts.admin', ['title' => 'Laporan Produksi Farmasi Tahun ' . now()->format('Y')])
            ->section('content');
    }

    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

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
            array_merge(['Pendapatan Alkes Farmasi dan Unit'], []),
            array_merge(['Retur Obat'], $this->totalReturObat),
            array_merge(['Pembelian Farmasi'], $this->totalPembelianFarmasi),
            array_merge(['Retur Supplier'], []),
            array_merge(['TOTAL PEMBELIAN (Pembelian Farmasi - Retur Supplier)'], []),
            array_merge(['Pemakaian BHP'], $this->stokKeluarMedis),
            array_merge(['Transfer Order'], []),
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