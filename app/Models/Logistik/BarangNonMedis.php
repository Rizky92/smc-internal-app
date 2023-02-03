<?php

namespace App\Models\Logistik;

use App\Models\Satuan;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class BarangNonMedis extends Model
{
    use Searchable, Sortable;
    
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrsbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeDenganMinmax(Builder $query, bool $export = false): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $selectQuery = "
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            IFNULL(ipsrssuplier.kode_suplier, '-') kode_supplier,
            IFNULL(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), IFNULL(IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0), 0) saran_order,
            ipsrsbarang.harga,
            IF(ipsrsbarang.stok <= IFNULL({$db}.ipsrs_minmax_stok_barang.stok_min, 0), ipsrsbarang.harga * (IFNULL({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok), 0) total_harga
        ";

        if ($export) {
            $selectQuery = str_replace("IFNULL(ipsrssuplier.kode_suplier, '-') kode_supplier,", '', $selectQuery);
        }

        return $query->selectRaw($selectQuery)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}.ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}.ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}.ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1');
    }

    public function scopeDaruratStok(Builder $query, bool $saranOrderNol = true): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        return $query->selectRaw("
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            IFNULL(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            IFNULL({$db}ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            IFNULL({$db}ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            IFNULL(IFNULL({$db}ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') saran_order,
            ipsrsbarang.harga,
            (ipsrsbarang.harga * (IFNULL({$db}ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok)) total_harga
        ")
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1')
            ->where('ipsrsbarang.stok', '<=', DB::raw("IFNULL({$db}ipsrs_minmax_stok_barang.stok_min, 0)"))
            ->when(!$saranOrderNol, fn (Builder $query) => $query->whereRaw("IFNULL(IFNULL({$db}ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') > 0"));
    }
}
