<?php

namespace App\Jobs;

use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Support\Str;

class ExportLaporanStatistikJob extends ExcelExportJob
{
    protected function filename(): string
    {
        return 'laporan-statistik';
    }

    protected function successMessage(): string
    {
        return 'Export Laporan Statistik ready for download';
    }

    protected function failureMessage(): string
    {
        return 'Export Laporan Statistik Failed';
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No RM',
            'Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Rawat',
            'Kamar',
            'Pasien Lama / Baru',
            'Asal Poli',
            'Dokter Poli',
            'Status Ralan',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD-10 Utama',
            'ICD-10 Sekunder',
            'Diagnosa Utama',
            'Diagnosa Sekunder',
            'ICD-9',
            'Kode Tindakan Ralan',
            'Tindakan Ralan',
            'Kode Tindakan Ranap',
            'Tindakan Ranap',
            'Lama Operasi',
            'Rujukan Masuk',
            'DPJP Ranap',
            'DPJP Anastesi',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'Rujukan Keluar',
            'Alamat',
            'No. HP',
            'Kunjungan ke',
            'Kode Penjamin',
        ];
    }

    protected function pageHeaders(): array
    {
        ['tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] = $this->payload;

        $periodeAwal = carbon($tglAwal);
        $periodeAkhir = carbon($tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Statistik Rekam Medis',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }

    protected function dataPerSheet(): array
    {
        ['tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] = $this->payload;

        return [
            fn () => RegistrasiPasien::query()
                ->laporanStatistik($tglAwal, $tglAkhir)
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    Str::transliterate($model->no_rawat ?? ''),
                    Str::transliterate($model->no_rm ?? ''),
                    Str::transliterate($model->nm_pasien ?? ''),
                    Str::transliterate($model->no_ktp ?? ''),
                    Str::transliterate($model->jk ?? ''),
                    Str::transliterate($model->tgl_lahir ?? ''),
                    Str::transliterate($model->umurdaftar.' '.$model->sttsumur ?? ''),
                    Str::transliterate($model->agama ?? ''),
                    Str::transliterate($model->suku ?? ''),
                    Str::transliterate($model->status_lanjut ?? ''),
                    Str::transliterate($model->ruangan ?? ''),
                    Str::transliterate($model->status_poli ?? ''),
                    Str::transliterate($model->nm_poli ?? ''),
                    Str::transliterate($model->nm_dokter ?? ''),
                    Str::transliterate($model->status ?? ''),
                    Str::transliterate($model->tgl_registrasi ?? ''),
                    Str::transliterate($model->jam_registrasi ?? ''),
                    Str::transliterate($model->tgl_keluar ?? ''),
                    Str::transliterate($model->jam_keluar ?? ''),
                    Str::transliterate($model->diagnosa_awal ?? ''),
                    Str::transliterate($this->diagnosaUtama($model->icd_diagnosa ?? '')),
                    Str::transliterate($this->diagnosaSekunder($model->icd_diagnosa ?? '')),
                    Str::transliterate($this->diagnosaUtama($model->diagnosa ?? '')),
                    Str::transliterate($this->diagnosaSekunder($model->diagnosa ?? '')),
                    Str::transliterate($model->icd9_prosedur ?? ''),
                    Str::transliterate($model->kd_tindakan_ralan ?? ''),
                    Str::transliterate($model->nm_tindakan_ralan ?? ''),
                    Str::transliterate($model->kd_tindakan_ranap ?? ''),
                    Str::transliterate($model->nm_tindakan_ranap ?? ''),
                    Str::transliterate($model->lama_operasi ?? ''),
                    Str::transliterate($model->rujukan_masuk ?? ''),
                    Str::transliterate($model->dokter_pj ?? ''),
                    Str::transliterate($model->dpjp_anastesi ?? ''),
                    Str::transliterate($model->kelas ?? ''),
                    Str::transliterate($model->penjamin ?? ''),
                    Str::transliterate($model->status_bayar ?? ''),
                    Str::transliterate($model->status_pulang_ranap ?? ''),
                    Str::transliterate($model->rujuk_keluar_rs ?? ''),
                    Str::transliterate($model->alamat ?? ''),
                    Str::transliterate($model->no_hp ?? ''),
                    $model->kunjungan_ke,
                    Str::transliterate($model->kd_pj ?? ''),
                ]),
        ];
    }

    private function diagnosaUtama(string $value): string
    {
        return str($value)->split('/(; )/')->first() ?? '';
    }

    private function diagnosaSekunder(string $value): string
    {
        return str($value)->split('/(; )/')->slice(1)->implode('; ');
    }
}
