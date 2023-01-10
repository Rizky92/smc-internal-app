<?php

namespace App\Support\Traits\Livewire;

use Illuminate\Support\Str;
use Rizky92\Xlswriter\ExcelExport;

trait ExcelExportable
{
    public function initializeExcelExportable()
    {
        $this->listeners = array_merge($this->listeners, [
            'notifyExportToComponent',
            'beginExport',
        ]);
    }

    abstract protected function filename(): string;

    abstract protected function dataPerSheet(): array;

    abstract protected function columnHeaders(): array;

    protected function getPageHeaders(): array
    {
        return [];
    }

    public function notifyExportToComponent()
    {
        if (method_exists($this, 'flashInfo')) {
            $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');
        }

        $this->emit('beginExport');
    }

    public function beginExport()
    {
        // $filename = now()->format('Ymd_His') . '_';

        // $filename .= Str::snake($this->filename());

        // $filename .= '.xlsx';

        // $sheet1 = ResepDokter::kunjunganResepObatRegular($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();
        // $sheet2 = ResepDokterRacikan::kunjunganResepObatRacikan($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();

        // $titles = [
        //     'RS Samarinda Medika Citra',
        //     'Laporan Kunjungan Resep Pasien',
        //     now()->format('d F Y'),
        // ];

        // $columnHeaders = [
        //     'No. Resep',
        //     'Dokter Peresep',
        //     'Tgl. Validasi',
        //     'Jam',
        //     'Pasien',
        //     'Jenis Perawatan',
        //     'Total Pembelian (RP)',
        // ];

        // $excel = ExcelExport::make($filename, 'Obat Regular')
        //     ->setPageHeaders($titles)
        //     ->setColumnHeaders($columnHeaders)
        //     ->setData($sheet1);

        // $excel->useSheet('Obat Racikan')
        //     ->setData($sheet2);

        // return $excel->export();
    }
}