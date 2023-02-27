<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TambahanBiaya extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'tambahan_biaya';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeBiayaTambahanUntukHonorDokter(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            reg_periksa.tgl_registrasi,
            reg_periksa.jam_reg,
            pasien.nm_pasien,
            reg_periksa.no_rkm_medis,
            tambahan_biaya.no_rawat,
            tambahan_biaya.nama_biaya,
            tambahan_biaya.besar_biaya,
            penjab.png_jawab,
            dokter.nm_dokter dokter_ralan,
            coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-') dokter_ranap,
            poliklinik.nm_poli,
            reg_periksa.status_lanjut,
            reg_periksa.status_bayar
        ")
            ->leftJoin('reg_periksa', 'tambahan_biaya.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dpjp_ranap', 'reg_periksa.no_rawat', '=', 'dpjp_ranap.no_rawat')
            ->leftJoin(DB::raw('dokter dokter_pj'), 'dpjp_ranap.kd_dokter', '=', 'dokter_pj.kd_dokter')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir]);
    }
}
