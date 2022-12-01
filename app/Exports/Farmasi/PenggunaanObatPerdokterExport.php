<?php

namespace App\Exports\Farmasi;

use App\Models\Farmasi\Resep;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenggunaanObatPerdokterExport implements FromCollection, WithHeadings
{
    use Exportable;
    
    private $periodeAwal;
    private $periodeAkhir;

    public function __construct(string $periodeAwal, string $periodeAkhir)
    {
        $this->periodeAwal = $periodeAwal;
        $this->periodeAkhir = $periodeAkhir;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Resep::penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir)
            ->cursor();
    }

    public function headings(): array
    {
        return [
            'No. Resep',
            'Tgl. Validasi',
            'Jam',
            'Nama Obat',
            'Jumlah',
            'Dokter Peresep',
            'Asal',
            'Asal Poli',
        ];
    }
}
