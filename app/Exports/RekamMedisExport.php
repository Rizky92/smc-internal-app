<?php

namespace App\Exports;

use App\Models\RekamMedis;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekamMedisExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $periodeAwal;
    private $periodeAkhir;

    public function __construct($periodeAwal = null, $periodeAkhir = null)
    {
        $this->periodeAwal = $periodeAwal ?? now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = $periodeAkhir ?? now()->endOfMonth()->format('Y-m-d');
    }

    public function collection()
    {
        return RekamMedis::query()
            ->whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->cursor();
    }

    public function headings(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Perawatan',
            'Pasien Lama / Baru',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD Diagnosa',
            'Diagnosa',
            'ICD Tindakan',
            'Tindakan',
            'Lama Operasi',
            'Rujukan Masuk',
            'DPJP',
            'Poli',
            'Kelas',
            'Penjamin',
            'Status Pulang',
            'Rujuk keluar ke RS',
            'No. HP',
            'Alamat',
            'Kunjungan ke',
        ];
    }
}
