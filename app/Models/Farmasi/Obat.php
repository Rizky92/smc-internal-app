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

    public function scopeDaruratStok(Builder $query, string $cari = '', bool $exportExcel = false): Builder
    {
        $sqlSelect = [
            'databarang.kode_brng',
            'nama_brng',
            'kodesatuan.satuan satuan_kecil',
            'kategori_barang.nama kategori',
            'stokminimal',
            'ifnull(round(stok_gudang.stok_di_gudang, 2), 0) stok_sekarang',
            '(databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0)) saran_order',
            'industrifarmasi.nama_industri',
            'round(databarang.h_beli) harga_beli',
            'round((databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0)) * databarang.h_beli) harga_beli_total',
        ];

        if ($exportExcel) {
            array_shift($sqlSelect);
        }

        return $query
            ->selectRaw(collect($sqlSelect)->join(','))
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoin(DB::raw("(
                select
                    kode_brng,
                    sum(stok) stok_di_gudang
                from gudangbarang
                inner join bangsal on gudangbarang.kd_bangsal = bangsal.kd_bangsal
                where bangsal.status = '1'
                and gudangbarang.kd_bangsal = 'ap'
                group by kode_brng
            ) stok_gudang"), 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1')
            ->where('stokminimal', '>', '0')
            ->whereRaw('(databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0)) > 0')
            ->whereRaw('ifnull(stok_gudang.stok_di_gudang, 0) <= stokminimal')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('databarang.kode_brng', 'like', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'like', "%{$cari}%")
                        ->orWhere('kategori_barang.nama', 'like', "%{$cari}%")
                        ->orWhere('industrifarmasi.nama_industri', 'like', "%{$cari}%");
                });
            });
    }
}
