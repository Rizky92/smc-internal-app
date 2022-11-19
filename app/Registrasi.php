<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Registrasi extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLaporanKunjungan(Builder $query, $statusPeriksa, $perawatanLanjutan)
    {
        // SELECT
        //     reg_periksa.no_rawat,
        //     reg_periksa.tgl_registrasi,
        //     reg_periksa.stts_daftar,
        //     dokter.nm_dokter,
        //     reg_periksa.no_rkm_medis,
        //     pasien.nm_pasien,
        //     poliklinik.nm_poli,
        //     concat(
        //         pasien.alamat,
        //         ', ',
        //         kelurahan.nm_kel,
        //         ', ',
        //         kecamatan.nm_kec,
        //         ', ',
        //         kabupaten.nm_kab
        //     ) almt_pj,
        //     pasien.jk,
        //     concat(
        //         reg_periksa.umurdaftar,
        //         ' ',
        //         reg_periksa.sttsumur
        //     ) AS umur,
        //     pasien.tgl_daftar
        // FROM
        //     reg_periksa
        //     INNER JOIN dokter ON reg_periksa.kd_dokter = dokter.kd_dokter
        //     INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
        //     INNER JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
        //     INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
        //     INNER JOIN kabupaten ON pasien.kd_kab = kabupaten.kd_kab
        //     INNER JOIN kecamatan ON pasien.kd_kec = kecamatan.kd_kec
        //     INNER JOIN kelurahan ON pasien.kd_kel = kelurahan.kd_kel
        // WHERE
        //     reg_periksa.status_lanjut = 'Ralan'
        //     AND reg_periksa.stts != 'Batal'
        // ORDER BY
        //     reg_periksa.tgl_registrasi,
        //     reg_periksa.jam_reg
        return $query->selectRaw('COUNT(reg_periksa), tgl_registrasi')
            ->where('status_lanjut', 'Ralan')
            ->where('stts', '!=', 'Batal')
            
    }
}
