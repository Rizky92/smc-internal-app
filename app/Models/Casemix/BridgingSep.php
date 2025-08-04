<?php

namespace App\Models\Casemix;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BridgingSep extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'bridging_sep';

    protected $primaryKey = 'no_sep';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePasienBatal(Builder $query, string $tglAwal, string $tglAkhir)
    {
        $sqlSelect = <<<SQL
            bridging_sep.no_sep no_sep,
            bridging_sep.no_rawat no_rawat,
            bridging_sep.tglsep tglsep,
            IF(bridging_sep.jnspelayanan = '1', '1. Ranap', '2. Ralan') jenis_pelayanan,
            reg_periksa.status_lanjut status_lanjut,
            reg_periksa.no_rkm_medis no_rm,
            pasien.nm_pasien nama_pasien,
            reg_periksa.status_bayar status_bayar,
            reg_periksa.stts status_periksa,
            penjab.png_jawab jaminan_registrasi
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', function ($join) {
                $join->on('bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
                     ->whereRaw("IF(bridging_sep.jnspelayanan = '1', 'Ranap', 'Ralan') = reg_periksa.status_lanjut");
            })
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->whereBetween('bridging_sep.tglsep', [$tglAwal, $tglAkhir])
            ->where('reg_periksa.stts', 'Batal')
            ->orderBy('bridging_sep.no_sep');
    }

    public function scopeRegistrasiCob(Builder $query, string $tglAwal, string $tglAkhir)
    {
        $sqlSelect = <<<SQL
            bridging_sep.no_sep no_sep,
            bridging_sep.tglsep tglsep,
            reg_periksa.no_rawat no_rawat,
            concat(bridging_sep.jnspelayanan, '. ', reg_periksa.status_lanjut) jenis_pelayanan,
            reg_periksa.no_rkm_medis no_rm,
            pasien.nm_pasien nama_pasien,
            penjab.png_jawab jaminan_registrasi
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('reg_periksa', function($join) {
                $join->on('bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
                     ->whereRaw("bridging_sep.jnspelayanan = IF(reg_periksa.status_lanjut = 'Ranap', '1', '2')");
            })
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('bridging_sep.tglsep', [$tglAwal, $tglAkhir])
            ->where('reg_periksa.kd_pj', '!=', 'BPJ');
    }

    public function scopeTriaseIgdZonaHijau(Builder $query, string $tglAwal, string $tglAkhir)
    {
        $sqlSelect = <<<SQL
            data_triase_igd.no_rawat no_rawat,
            reg_periksa.no_rkm_medis no_rkm_medis,
            pasien.nm_pasien nm_pasien,
            penjab.png_jawab png_jawab,
            data_triase_igd.tgl_kunjungan tgl_kunjungan,
            data_triase_igd.cara_masuk cara_masuk,
            data_triase_igd.alasan_kedatangan alasan_kedatangan,
            master_triase_macam_kasus.macam_kasus macam_kasus,
            data_triase_igdsekunder.plan plan
        SQL;
        return $query
            ->selectRaw($sqlSelect)
            ->from('data_triase_igd')
            ->join('master_triase_macam_kasus', 'data_triase_igd.kode_kasus', '=', 'master_triase_macam_kasus.kode_kasus')
            ->join('reg_periksa', 'data_triase_igd.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('data_triase_igdsekunder', 'data_triase_igd.no_rawat', '=', 'data_triase_igdsekunder.no_rawat')
            ->whereBetween('data_triase_igd.tgl_kunjungan', [$tglAwal, $tglAkhir])
            ->where('data_triase_igdsekunder.plan', 'like', '%hijau%');
    }
}
