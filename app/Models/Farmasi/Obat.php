<?php

namespace App\Models\Farmasi;

use App\Models\Farmasi\Inventaris\GudangObat;
use App\Models\Farmasi\Inventaris\IndustriFarmasi;
use App\Models\Satuan;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Obat extends Model
{
    use Searchable, Sortable;

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'databarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeDaruratStok(Builder $query, bool $exportExcel = false): Builder
    {
        return $query
            ->with(['satuanKecil', 'satuanBesar', 'kategori', 'industriFarmasi'])
            ->withSum(['gudang' => fn (Builder $q) => $q->whereRelation('bangsal', 'status', '1')], 'stok')
            ->where('status', '1')
            ->where('stokminimal', '>', 0);

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

        $stokGudang = DB::raw("(
            select kode_brng, sum(stok) stok_di_gudang
            from gudangbarang
            inner join bangsal on gudangbarang.kd_bangsal = bangsal.kd_bangsal
            where bangsal.status = '1'
            and gudangbarang.kd_bangsal = 'ap'
            group by kode_brng
        ) stok_gudang");

        if ($exportExcel) {
            array_shift($sqlSelect);
        }

        return $query
            ->selectRaw(collect($sqlSelect)->join(','))
            ->join('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->join('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->join('industrifarmasi', 'databarang.kode_industri', '=', 'industrifarmasi.kode_industri')
            ->leftJoin($stokGudang, 'databarang.kode_brng', '=', 'stok_gudang.kode_brng')
            ->where('status', '1')
            ->where('stokminimal', '>', '0')
            ->whereRaw('(databarang.stokminimal - ifnull(stok_gudang.stok_di_gudang, 0)) > 0')
            ->whereRaw('ifnull(stok_gudang.stok_di_gudang, 0) <= stokminimal');
    }
}
