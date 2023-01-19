<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegistrasiPasien extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeSelectDaftarPasienRanap(Builder $query, bool $exportToExcel = false): Builder
    {
        $sqlSelect = "
            kamar_inap.kd_kamar,
            reg_periksa.no_rawat,
            reg_periksa.no_rkm_medis,
            concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) ruangan,
            kamar.kelas,
            concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')') data_pasien,
            concat(pasien.alamat, ', Kel. ', kelurahan.nm_kel, ', Kec. ', kecamatan.nm_kec, ', ', kabupaten.nm_kab, ', ', propinsi.nm_prop) alamat_pasien,
            pasien.agama,
            concat(pasien.namakeluarga, ' (', pasien.keluarga, ')') pj,
            penjab.png_jawab,
            poliklinik.nm_poli,
            dokter.nm_dokter dokter_poli,
            kamar_inap.stts_pulang,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar) tgl_keluar,
            if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar) jam_keluar,
            kamar_inap.trf_kamar,
            kamar_inap.lama,
            kamar_inap.ttl_biaya,
            ( SELECT dokter.nm_dokter FROM dokter JOIN dpjp_ranap ON dpjp_ranap.kd_dokter = dokter.kd_dokter WHERE dpjp_ranap.no_rawat = reg_periksa.no_rawat LIMIT 1 ) nama_dokter,
            pasien.no_tlp
        ";

        if ($exportToExcel) {
            $sqlSelect = Str::after($sqlSelect, "kamar_inap.kd_kamar,");
        }

        return $query->selectRaw($sqlSelect)
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->join('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter');
    }

    public function scopeSelectLaporanPasienRanap(Builder $query): Builder
    {
        return $query->selectRaw("
                reg_periksa.no_rawat,
                reg_periksa.tgl_registrasi,
                reg_periksa.jam_reg,
                reg_periksa.no_rkm_medis,
                kamar.kelas,
                concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) ruangan,
                concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')') data_pasien,
                penjab.png_jawab,
                poliklinik.nm_poli,
                dokter.nm_dokter dokter_poli,
                kamar_inap.stts_pulang,
                kamar_inap.tgl_masuk,
                kamar_inap.jam_masuk,
                if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar) tgl_keluar,
                if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar) jam_keluar,
                ( SELECT dokter.nm_dokter FROM dokter JOIN dpjp_ranap ON dpjp_ranap.kd_dokter = dokter.kd_dokter WHERE dpjp_ranap.no_rawat = reg_periksa.no_rawat LIMIT 1 ) nama_dokter
            ")
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->join('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter');
    }

    // TODO: simplify scope yang ini
    public function scopeFilterDaftarPasienRanap(
        Builder $query,
        string $cari = '',
        string $tglAwal = '',
        string $tglAkhir = '',
        string $statusPerawatan = '-'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        return $query
            ->when($statusPerawatan === '-', function (Builder $query) {
                return $query->where('kamar_inap.stts_pulang', '-');
            })
            ->when($statusPerawatan !== '-', function (Builder $query) use ($statusPerawatan, $tglAwal, $tglAkhir) {
                switch (Str::snake($statusPerawatan)) {
                    case 'tanggal_masuk':
                        return $query->whereBetween('kamar_inap.tgl_masuk', [$tglAwal, $tglAkhir]);
                    case 'tanggal_keluar':
                        return $query->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir]);
                }
            })
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->whereRaw("concat(
                    reg_periksa.no_rawat, ' ',
                    reg_periksa.no_rkm_medis, ' ',
                    kamar.kd_kamar, ' ',
                    kamar.kelas, ' ',
                    bangsal.kd_bangsal, ' ',
                    bangsal.nm_bangsal, ' ',
                    kamar_inap.stts_pulang, ' ',
                    pasien.nm_pasien, ' ',
                    pasien.agama, ' ',
                    pasien.alamat, ' ',
                    pasien.no_tlp, ' ',
                    kelurahan.nm_kel, ' ',
                    kecamatan.nm_kec, ' ',
                    kabupaten.nm_kab, ' ',
                    propinsi.nm_prop, ' ',
                    penjab.png_jawab, ' ',
                    poliklinik.kd_poli, ' ',
                    poliklinik.nm_poli, ' ',
                    dokter.nm_dokter
                ) like ?", "%{$cari}%");
            });
    }

    public function scopeFilterLaporanPasienRanap(
        Builder $query,
        string $cari = '',
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jamAwal = '',
        string $jamAkhir = '',
        string $statusPerawatan = '',
        bool $pasienPindahKamar = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $subQueryTglDanJamPerawatan = function ($query) use ($statusPerawatan, $tglAwal, $tglAkhir, $jamAwal, $jamAkhir) {
            switch (Str::snake($statusPerawatan)) {
                case 'tanggal_masuk':
                    return $query->whereBetween('kamar_inap.tgl_masuk', [$tglAwal, $tglAkhir])
                        ->where(
                            fn ($query) => $query
                                ->where('kamar_inap.jam_masuk', '>=', $jamAwal)
                                ->orWhere('kamar_inap.jam_masuk', '<=', $jamAkhir)
                        );

                case 'tanggal_keluar':
                    return $query->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir])
                        ->where(
                            fn ($query) => $query
                                ->where('kamar_inap.jam_keluar', '>=', $jamAwal)
                                ->orWhere('kamar_inap.jam_keluar', '<=', $jamAkhir)
                        );
            }
        };

        $subQueryStatusPindahKamar = fn ($query) => $query->where('stts_pulang', 'Pindah Kamar');

        return $query
            ->where($subQueryTglDanJamPerawatan)
            ->when(
                $pasienPindahKamar,
                fn (Builder $query) => $query->whereIn('reg_periksa.no_rawat', fn ($query) => $query
                    ->select('no_rawat')
                    ->from('kamar_inap')
                    ->where($subQueryStatusPindahKamar)),
                fn (Builder $query) => $query->whereNotIn('reg_periksa.no_rawat', fn ($query) => $query
                    ->select('no_rawat')
                    ->from('kamar_inap')
                    ->where($subQueryStatusPindahKamar)),
            )
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->whereRaw("concat(
                    reg_periksa.no_rawat, ' ',
                    reg_periksa.no_rkm_medis, ' ',
                    kamar.kd_kamar, ' ',
                    kamar.kelas, ' ',
                    bangsal.kd_bangsal, ' ',
                    bangsal.nm_bangsal, ' ',
                    kamar_inap.stts_pulang, ' ',
                    pasien.nm_pasien, ' ',
                    pasien.agama, ' ',
                    pasien.alamat, ' ',
                    pasien.no_tlp, ' ',
                    kelurahan.nm_kel, ' ',
                    kecamatan.nm_kec, ' ',
                    kabupaten.nm_kab, ' ',
                    propinsi.nm_prop, ' ',
                    penjab.png_jawab, ' ',
                    poliklinik.kd_poli, ' ',
                    poliklinik.nm_poli, ' ',
                    dokter.nm_dokter
                ) like ?", "%{$cari}%");
            });
    }
}
