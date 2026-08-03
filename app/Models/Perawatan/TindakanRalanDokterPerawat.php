<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Support\AlkesProcedures;
use Illuminate\Database\Eloquent\Builder;

class TindakanRalanDokterPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_drpr';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_rawat',
        'kd_jenis_prw',
        'kd_dokter',
        'nip',
        'tgl_perawatan',
    ];

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_jl_drpr.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            rawat_jl_drpr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (rawat_jl_drpr.biaya_rawat * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            rawat_jl_drpr.kd_jenis_prw,
            'Tindakan Ralan DrPr' as kategori,
            5 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'rawat_jl_drpr.no_rawat'))
            ->groupBy(['rawat_jl_drpr.no_rawat', 'rawat_jl_drpr.kd_jenis_prw', 'jns_perawatan.nm_perawatan', 'rawat_jl_drpr.biaya_rawat']);
    }

    public function scopePenggunaanAlkes(Builder $query, string $tglAwal, string $tglAkhir, string $nama): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth();
        }

        $sqlSelect = <<<'SQL'
            rawat_jl_drpr.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            rawat_jl_drpr.kd_jenis_prw,
            jns_perawatan.nm_perawatan,
            rawat_jl_drpr.kd_dokter as nakes,
            dokter.nm_dokter as nama_nakes,
            rawat_jl_drpr.tgl_perawatan as tgl_periksa,
            rawat_jl_drpr.jam_rawat as jam,
            reg_periksa.kd_pj,
            penjab.png_jawab,
            poliklinik.nm_poli as unit,
            rawat_jl_drpr.biaya_rawat as biaya,
            'Ralan' as status
            SQL;

        $this->addSearchConditions([
            'reg_periksa.no_rkm_medis',
            'pasien.nm_pasien',
            'jns_perawatan.nm_perawatan',
            'dokter.nm_dokter',
            'petugas.nama',
            'reg_periksa.kd_pj',
            'poliklinik.nm_poli',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'rawat_jl_drpr.kd_dokter', 'dokter.kd_dokter')
            ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw', 'jns_perawatan.kd_jenis_prw')
            ->join('reg_periksa', 'rawat_jl_drpr.no_rawat', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', 'poliklinik.kd_poli')
            ->whereBetween('rawat_jl_drpr.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->whereIn('rawat_jl_drpr.kd_jenis_prw', AlkesProcedures::ids($this->getConnectionName(), 'jns_perawatan', $nama));
    }
}
