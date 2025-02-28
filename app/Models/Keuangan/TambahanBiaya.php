<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TambahanBiaya extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'tambahan_biaya';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeBiayaTambahanUntukHonorDokter(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
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
            SQL;

        $this->addSearchConditions([
            'pasien.nm_pasien',
            'reg_periksa.no_rkm_medis',
            'tambahan_biaya.no_rawat',
            'tambahan_biaya.nama_biaya',
            'penjab.png_jawab',
            'dokter.nm_dokter',
            "coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')",
            'poliklinik.nm_poli',
            'reg_periksa.status_lanjut',
            'reg_periksa.status_bayar',
        ]);

        $this->addRawColumns([
            'dokter_ralan' => DB::raw('dokter.nm_dokter'),
            'dokter_ranap' => DB::raw("coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')"),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['besar_biaya' => 'float'])
            ->leftJoin('reg_periksa', 'tambahan_biaya.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dpjp_ranap', 'reg_periksa.no_rawat', '=', 'dpjp_ranap.no_rawat')
            ->leftJoin(DB::raw('dokter dokter_pj'), 'dpjp_ranap.kd_dokter', '=', 'dokter_pj.kd_dokter')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir]);
    }

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            tambahan_biaya.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            tambahan_biaya.nama_biaya as nama_barang_jasa,
            '' as nama_satuan_ukur,
            tambahan_biaya.besar_biaya as harga_satuan,
            1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            tambahan_biaya.besar_biaya as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            '' as kd_jenis_prw,
            'Tambahan Biaya' as kategori,
            13 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'tambahan_biaya.no_rawat'));
    }
}
