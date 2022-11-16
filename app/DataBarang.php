<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataBarang extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeJanganTampilkanStokMinimalNol(Builder $query)
    {
        return $query->where('stokminimal', '>', 0);
    }

    public function scopeDaruratStok(Builder $query)
    {
        return $query
            ->selectRaw("
                databarang.kode_brng,
                nama_brng,
                isi,
                satuankecil.satuan satuan_kecil,
                satuanbesar.satuan satuan_besar,
                kategori_barang.nama kategori,
                stokminimal,
                stok_gudang.stok_di_gudang,
                (databarang.stokminimal - stok_gudang.stok_di_gudang) saran_order
            ")
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join(DB::raw('kodesatuan satuankecil'), 'databarang.kode_sat', '=', 'satuankecil.kode_sat')
            ->join(DB::raw('kodesatuan satuanbesar'), 'databarang.kode_satbesar', '=', 'satuanbesar.kode_sat')
            ->join(DB::raw("(
                SELECT
                    kode_brng,
                    SUM(stok) stok_di_gudang
                FROM gudangbarang
                INNER JOIN bangsal ON gudangbarang.kd_bangsal = bangsal.kd_bangsal
                WHERE bangsal.status = '1'
                AND bangsal.kd_bangsal IN ('IFG', 'IFA', 'AP')
                GROUP BY kode_brng
            ) stok_gudang"), 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1');
    }
}
