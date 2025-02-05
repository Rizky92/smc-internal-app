<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ObatPulang extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'resep_pulang';

    protected $primaryKey = false;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            resep_pulang.no_rawat,
            case
                when reg_periksa.status_lanjut = 'Ranap' then '080'
                when reg_periksa.status_lanjut = 'Ralan' and reg_periksa.kd_pj = 'BPJ' then '030'
                else '040'
            end as kode_transaksi,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            resep_pulang.harga as harga_satuan,
            sum(resep_pulang.jml_barang) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(resep_pulang.total) as dpp,
            0 as ppn_persen,
            0 as ppn_nominal,
            resep_pulang.kode_brng as kd_jenis_prw,
            'Obat Pulang' as kategori,
            15 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'resep_pulang.kode_brng', '=', 'databarang.kode_brng')
            ->join('reg_periksa', 'resep_pulang.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'resep_pulang.no_rawat'))
            ->groupBy(['resep_pulang.no_rawat', 'resep_pulang.kode_brng', 'databarang.nama_brng', 'resep_pulang.harga']);
    }
}
