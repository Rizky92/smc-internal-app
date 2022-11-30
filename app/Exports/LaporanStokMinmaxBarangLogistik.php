<?php

namespace App\Exports;

use App\Models\Nonmedis\BarangNonmedis;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanStokMinmaxBarangLogistik implements FromCollection, WithHeadings
{
    use Exportable;

    private $timestamp;
    private $cari;
    private $saranOrderNol;

    public function __construct($timestamp = null, $cari = null, $saranOrderNol = true)
    {
        $this->timestamp = $timestamp ?? now()->format('Ymd_His');
        $this->cari = $cari;
        $this->saranOrderNol = $saranOrderNol;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BarangNonmedis::laporanDaruratStok($this->cari, $this->saranOrderNol)
            ->cursor();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'Jenis',
            'Supplier',
            'Min',
            'Max',
            'Saat ini',
            'Saran order',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];
    }
}
