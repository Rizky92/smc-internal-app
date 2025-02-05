<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReturObatDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detreturjual';

    protected $primaryKey = null;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            left(detreturjual.no_retur_jual, 17) as no_rawat,
            case
                when reg_periksa.status_lanjut = 'Ranap' then '080'
                when reg_periksa.status_lanjut = 'Ralan' and reg_periksa.kd_pj = 'BPJ' then '030'
                else '040'
            end as kode_transaksi,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            detreturjual.h_retur as harga_satuan,
            sum(detreturjual.jml_retur) * -1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(detreturjual.subtotal) * -1 as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            detreturjual.kode_brng as kd_jenis_prw,
            'Pemberian Obat' as kategori,
            19 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'detreturjual.kode_brng', '=', 'databarang.kode_brng')
            ->join('reg_periksa', DB::raw('left(detreturjual.no_retur_jual, 17)'), '=', 'reg_periksa.no_rawat')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', DB::raw('left(detreturjual.no_retur_jual, 17)')))
            ->groupBy(['detreturjual.no_retur_jual', 'detreturjual.kode_brng', 'databarang.nama_brng', 'detreturjual.h_retur']);
    }
}
