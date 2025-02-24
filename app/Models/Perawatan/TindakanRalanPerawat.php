<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRalanPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_pr';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_jl_pr.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            rawat_jl_pr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (rawat_jl_pr.biaya_rawat * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            rawat_jl_pr.kd_jenis_prw,
            'Tindakan Ralan Pr' as kategori,
            4 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'rawat_jl_pr.no_rawat'))
            ->groupBy(['rawat_jl_pr.no_rawat', 'rawat_jl_pr.kd_jenis_prw', 'jns_perawatan.nm_perawatan', 'rawat_jl_pr.biaya_rawat']);
    }
}
