<?php

namespace App\Exports;

use App\Models\Nonmedis\BarangNonmedis;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LogistikStokMinmaxBarang implements FromCollection, WithHeadings
{
    use Exportable;

    private $cari;

    public function __construct($cari = null)
    {
        $this->cari = $cari;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BarangNonmedis::denganMinmax($this->cari, true)
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
