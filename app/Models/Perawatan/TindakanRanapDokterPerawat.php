<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRanapDokterPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_inap_drpr';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_inap_drpr.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan_inap.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            rawat_inap_drpr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (rawat_inap_drpr.biaya_rawat * count(*)) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            rawat_inap_drpr.kd_jenis_prw,
            'Tindakan Ranap DrPr' as kategori,
            8 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'rawat_inap_drpr.no_rawat'))
            ->groupBy(['rawat_inap_drpr.no_rawat', 'rawat_inap_drpr.kd_jenis_prw', 'jns_perawatan_inap.nm_perawatan', 'rawat_inap_drpr.biaya_rawat']);
    }
}
