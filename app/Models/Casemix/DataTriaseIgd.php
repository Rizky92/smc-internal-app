<?php

namespace App\Models\Casemix;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DataTriaseIgd extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'data_triase_igd';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeTriaseIgdZonaHijau(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'data_triase_igd.no_rawat',
            'bridging_sep.no_sep',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.png_jawab',
            'data_triase_igd.tgl_kunjungan',
            'data_triase_igd.cara_masuk',
            'reg_periksa.status_lanjut',
            'data_triase_igd.alasan_kedatangan',
            'master_triase_macam_kasus.macam_kasus',
            'data_triase_igdsekunder.plan',
        ]);

        $sqlSelect = <<<'SQL'
            data_triase_igd.no_rawat no_rawat,
            bridging_sep.no_sep no_sep,
            reg_periksa.no_rkm_medis no_rkm_medis,
            reg_periksa.stts stts,
            pasien.nm_pasien nm_pasien,
            penjab.png_jawab png_jawab,
            data_triase_igd.tgl_kunjungan tgl_kunjungan,
            data_triase_igd.cara_masuk cara_masuk,
            reg_periksa.status_lanjut status_lanjut,
            data_triase_igd.alasan_kedatangan alasan_kedatangan,
            master_triase_macam_kasus.macam_kasus macam_kasus,
            data_triase_igdsekunder.plan plan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('master_triase_macam_kasus', 'data_triase_igd.kode_kasus', '=', 'master_triase_macam_kasus.kode_kasus')
            ->join('reg_periksa', 'data_triase_igd.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('bridging_sep', 'data_triase_igd.no_rawat', '=', 'bridging_sep.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('data_triase_igdsekunder', 'data_triase_igd.no_rawat', '=', 'data_triase_igdsekunder.no_rawat')
            ->whereBetween('data_triase_igd.tgl_kunjungan', [$tglAwal, $tglAkhir])
            ->where('data_triase_igdsekunder.plan', 'like', '%hijau%');
    }
}
