<?php

namespace App\Exports;

use App\Models\Perawatan\Registrasi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekamMedisExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private $periodeAwal;
    private $periodeAkhir;

    public function __construct($periodeAwal = null, $periodeAkhir = null)
    {
        $this->periodeAwal = $periodeAwal ?? now()->startOfMonth();
        $this->periodeAkhir = $periodeAkhir ?? now()->endOfMonth();
    }

    public function query()
    {
        return Registrasi::with('diagnosa')->laporanStatistikRekamMedis();
    }

    public function headings(): array
    {
        return [
            "No. Rawat",
            "No. RM",
            "Nama Pasien",
            "NIK",
            "L / P",
            "Tgl. Lahir",
            "Umur",
            "Agama",
            "Suku",
            "Jenis Perawatan",
            "Pasien Lama / Baru",
            "Tgl. Masuk",
            "Jam Masuk",
            "Tgl. Pulang",
            "Jam Pulang",
            "Diagnosa Masuk",
            "ICD Primer",
            "Diagnosa Primer",
            "ICD Sekunder",
            "Diagnosa Sekunder",
            "ICD 9CM",
            "Tindakan",
            "Lama Operasi",
            "Rujukan Masuk",
            "DPJP",
            "Poli",
            "Kelas",
            "Penjamin",
            "Status Pulang",
            "Rujuk keluar ke RS",
            "No. HP",
            "Alamat",
            "Kunjungan ke",
        ];
    }

    public function map($row): array
    {
        $diagnosa = $row->diagnosa->take(1 - $row->diagnosa->count());

        $kdDiagnosaSekunder = $diagnosa->reduce(function ($carry, $item) {
            return $item->kd_penyakit . '; <br>' . $carry;
        });

        $nmDiagnosaSekunder = $diagnosa->reduce(function ($carry, $item) {
            return $item->nm_penyakit . '; <br>' . $carry;
        });
        return [
            $row->no_rawat,
            $row->no_rkm_medis,
            $row->nm_pasien,
            $row->no_ktp,
            $row->jk,
            $row->tgl_lahir,
            $row->umur,
            $row->agama,
            $row->nama_suku_bangsa,
            $row->status_lanjut,
            $row->status_poli,
            $row->tgl_registrasi,
            $row->jam_reg,
            $row->tgl_keluar,
            $row->jam_keluar,
            $row->diagnosa_awal,
            optional(optional($row->diagnosa)->first())->kd_penyakit ?? '-',
            optional(optional($row->diagnosa)->first())->nm_penyakit ?? '-',
            $kdDiagnosaSekunder ?? '-',
            $nmDiagnosaSekunder ?? '-',
            $row->kode_tindakan,
            $row->nama_tindakan,
            '-',
            '-',
            $row->nm_dokter,
            $row->nm_poli,
            $row->kelas,
            $row->png_jawab,
            $row->stts,
            '-',
            $row->no_tlp,
            $row->alamat,
            $row->kunjungan_ke,
        ];
    }
}
