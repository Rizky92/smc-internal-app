<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PenjualanObatDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detailjual';

    protected $primaryKey = null;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            detailjual.nota_jual as no_rawat,
            '040' as kode_transaksi,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            detailjual.h_jual as harga_satuan,
            detailjual.jumlah as jumlah_barang_jasa,
            detailjual.dis as diskon_persen,
            detailjual.bsr_dis as diskon_nominal,
            detailjual.total as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            detailjual.kode_brng as kd_jenis_prw,
            'Jual Bebas' as kategori,
            16 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'detailjual.kode_brng', '=', 'databarang.kode_brng')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'detailjual.nota_jual'));
    }
}
