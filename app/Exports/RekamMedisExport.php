<?php

namespace App\Exports;

use App\Registrasi;
use Illuminate\Support\Str;
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
        return Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)
            ->orderBy('no_rawat')
            ->orderBy('no_reg');
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

    public function map($dataRekamMedis): array
    {
        /**@var \App\Registrasi $dataRekamMedis */

        $ranap = optional(optional($dataRekamMedis->rawatInap)->first());

        $kodeTindakan = $this->mapKodeTindakan($dataRekamMedis);

        $namaTindakan = $this->mapNamaTindakan($dataRekamMedis);

        $diagnosa = $this->mapDiagnosa($dataRekamMedis);

        return [
            $dataRekamMedis->no_rawat,
            $dataRekamMedis->no_rkm_medis,
            optional($dataRekamMedis->pasien)->nm_pasien,
            optional($dataRekamMedis->pasien)->no_ktp,
            optional($dataRekamMedis->pasien)->jk,
            optional($dataRekamMedis->pasien)->tgl_lahir,
            $dataRekamMedis->umurdaftar,
            optional($dataRekamMedis->pasien)->agama,
            optional(optional($dataRekamMedis->pasien)->suku)->nama_suku_bangsa ?? '-',
            $dataRekamMedis->status_lanjut,
            $dataRekamMedis->status_poli,
            $dataRekamMedis->tgl_registrasi,
            $dataRekamMedis->jam_reg,
            optional(optional($dataRekamMedis->rawatInap)->first())->pivot->tgl_keluar ?? '',
            optional(optional($dataRekamMedis->rawatInap)->first())->pivot->jam_keluar ?? '',
            optional(optional($dataRekamMedis->rawatInap)->first())->pivot->diagnosa_awal ?? '',
            $diagnosa['kdDiagnosaPrimer'],
            $diagnosa['nmDiagnosaPrimer'],
            $diagnosa['kdDiagnosaSekunder'],
            $diagnosa['nmDiagnosaSekunder'],
            $kodeTindakan ?? '-',
            $namaTindakan ?? '-',
            "-",
            "-",
            optional($dataRekamMedis->dokter)->nm_dokter ?? '-',
            optional($dataRekamMedis->poliklinik)->nm_poli ?? '-',
            optional(optional($dataRekamMedis->rawatInap)->first())->kelas ?? '-',
            optional($dataRekamMedis->penjamin)->png_jawab ?? '-',
            $dataRekamMedis->stts ?? '-',
            "-",
            optional($dataRekamMedis->pasien)->no_tlp ?? '-',
            optional($dataRekamMedis->pasien)->alamat ?? '-',
            $dataRekamMedis->kunjungan_ke ?? '-',
        ];
    }

    private function mapKodeTindakan(Registrasi $dataRekamMedis): string
    {
        $kdTindakanRalanDokter = $dataRekamMedis->tindakanRalanDokter->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        $kdTindakanRalanPerawat = $dataRekamMedis->tindakanRalanPerawat->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        $kdTindakanRalanDokterPerawat = $dataRekamMedis->tindakanRalanDokterPerawat->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        $kdTindakanRanapDokter = $dataRekamMedis->tindakanRanapDokter->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        $kdTindakanRanapPerawat = $dataRekamMedis->tindakanRanapPerawat->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        $kdTindakanRanapDokterPerawat = $dataRekamMedis->tindakanRanapDokterPerawat->reduce(function ($carry, $item) {
            return $item->kd_jenis_prw . '; ' . $carry;
        });

        return Str::of(
            collect([
                $kdTindakanRalanDokter,
                $kdTindakanRalanPerawat,
                $kdTindakanRalanDokterPerawat,
                $kdTindakanRanapDokter,
                $kdTindakanRanapPerawat,
                $kdTindakanRanapDokterPerawat
            ])->join('')
        )->trim();
    }

    private function mapNamaTindakan(Registrasi $dataRekamMedis): string
    {
        $nmTindakanRalanDokter = $dataRekamMedis->tindakanRalanDokter->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        $nmTindakanRalanPerawat = $dataRekamMedis->tindakanRalanPerawat->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        $nmTindakanRalanDokterPerawat = $dataRekamMedis->tindakanRalanDokterPerawat->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        $nmTindakanRanapDokter = $dataRekamMedis->tindakanRanapDokter->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        $nmTindakanRanapPerawat = $dataRekamMedis->tindakanRanapPerawat->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        $nmTindakanRanapDokterPerawat = $dataRekamMedis->tindakanRanapDokterPerawat->reduce(function ($carry, $item) {
            return $item->nm_perawatan . '; ' . $carry;
        });

        return Str::of(
            collect([
                $nmTindakanRalanDokter,
                $nmTindakanRalanPerawat,
                $nmTindakanRalanDokterPerawat,
                $nmTindakanRanapDokter,
                $nmTindakanRanapPerawat,
                $nmTindakanRanapDokterPerawat
            ])->join('')
        )->trim();
    }

    private function mapDiagnosa(Registrasi $dataRekamMedis)
    {
        $diagnosaSekunder = $dataRekamMedis->diagnosa->take(1 - $dataRekamMedis->diagnosa->count());

        $kdDiagnosaSekunder = $diagnosaSekunder->reduce(function ($carry, $item) {
            return $item->kd_penyakit . ';' . $carry;
        });

        $nmDiagnosaSekunder = $diagnosaSekunder->reduce(function ($carry, $item) {
            return $item->nm_penyakit . ';' . $carry;
        });

        return [
            'kdDiagnosaPrimer' => optional(optional($dataRekamMedis->diagnosa)->first())->kd_penyakit ?? '-',
            'nmDiagnosaPrimer' => optional(optional($dataRekamMedis->diagnosa)->first())->nm_penyakit ?? '-',
            'kdDiagnosaSekunder' => $kdDiagnosaSekunder ?? '-',
            'nmDiagnosaSekunder' => $nmDiagnosaSekunder ?? '-',
        ];
    }
}
