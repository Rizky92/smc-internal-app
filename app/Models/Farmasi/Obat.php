<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Obat extends Model
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

    public function scopeDaruratStok(Builder $query, string $cari = ''): Builder
    {
        return $query
            ->selectRaw("
                databarang.kode_brng,
                nama_brng,
                kodesatuan.satuan satuan_kecil,
                kategori_barang.nama kategori,
                stokminimal,
                IFNULL(ROUND(stok_gudang.stok_di_gudang, 2), 0) stok_sekarang,
                (databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) saran_order,
                industrifarmasi.nama_industri,
                CEIL(databarang.h_beli) harga_beli,
                CEIL((databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) * databarang.h_beli) harga_beli_total
            ")
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoin(DB::raw("(
                SELECT
                    kode_brng,
                    SUM(stok) stok_di_gudang
                FROM gudangbarang
                INNER JOIN bangsal ON gudangbarang.kd_bangsal = bangsal.kd_bangsal
                WHERE bangsal.status = '1'
                AND gudangbarang.kd_bangsal = 'AP'
                GROUP BY kode_brng
            ) stok_gudang"), 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1')
            ->where('stokminimal', '>', '0')
            ->whereRaw('(databarang.stokminimal - IFNULL(stok_gudang.stok_di_gudang, 0)) > 0')
            ->whereRaw('IFNULL(stok_gudang.stok_di_gudang, 0) <= stokminimal')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('databarang.kode_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('kategori_barang.nama kategori', 'LIKE', "%{$cari}%")
                        ->orWhere('industrifarmasi.nama_industri', 'LIKE', "%{$cari}%");
                });
            });
    }
}
