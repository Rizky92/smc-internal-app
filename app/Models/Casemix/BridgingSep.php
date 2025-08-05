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
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.no_rawat',
            'bridging_sep.tglsep',
            'reg_periksa.status_lanjut',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'reg_periksa.status_bayar',
            'reg_periksa.stts',
            'penjab.png_jawab',
        ]);

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
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $this->addSearchConditions([
            'bridging_sep.no_sep',
            'bridging_sep.tglsep',
            'reg_periksa.no_rawat',
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.png_jawab',
        ]);

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
}
