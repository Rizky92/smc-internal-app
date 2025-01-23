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

    public function scopeItemFakturPajak(Builder $query, string $kodePJ = 'BPJ'): Builder
    {
        $sqlSelect = <<<'SQL'
            left(detreturjual.no_retur_jual, 17) as no_rawat,
            ? as kode_transaksi,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            detreturjual.h_retur * -1 as harga_satuan,
            sum(detreturjual.jml_retur) * -1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(detreturjual.subtotal) * -1 as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            detreturjual.kode_brng as kd_jenis_prw,
            'Retur Obat' as kategori,
            'Ranap' as status_lanjut,
            19 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect, [($kodePJ === 'BPJ' ? '030' : '040')])
            ->join('databarang', 'detreturjual.kode_brng', '=', 'databarang.kode_brng')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', DB::raw('left(detreturjual.no_retur_jual, 17)')))
            ->groupBy(['detreturjual.no_retur_jual', 'detreturjual.kode_brng', 'databarang.nama_brng', 'detreturjual.h_retur']);
    }
}
