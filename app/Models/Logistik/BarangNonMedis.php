<?php

namespace App\Models\Logistik;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangNonMedis extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrsbarang';

    public $incrementing = false;

    public $timestamps = false;

    protected array $searchColumns = [
        'kode_brng',
        'nama_brng',
        'kode_sat',
        'jenis',
    ];

    public function scopeDenganMinmax(Builder $query, bool $export = false): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<SQL
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            ifnull(ipsrssuplier.kode_suplier, '-') kode_supplier,
            ifnull(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            if(
                ipsrsbarang.stok <= ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0),
                ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0),
                0
            ) saran_order,
            ipsrsbarang.harga,
            if(
                ipsrsbarang.stok <= ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0),
                ipsrsbarang.harga * (ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok),
                0
            ) total_harga
        SQL;

        if ($export) {
            $sqlSelect = str_replace("ifnull(ipsrssuplier.kode_suplier, '-') kode_supplier,", '', $sqlSelect);
        }

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}.ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}.ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}.ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1');
    }

    public function scopeDaruratStok(Builder $query, bool $saranOrderNol = true): Builder
    {
        $db = DB::connection('mysql_smc')->getDatabaseName();

        $sqlSelect = <<<SQL
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            ifnull(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') saran_order,
            ipsrsbarang.harga,
            (ipsrsbarang.harga * (ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok)) total_harga
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin("{$db}.ipsrs_minmax_stok_barang", 'ipsrsbarang.kode_brng', '=', "{$db}.ipsrs_minmax_stok_barang.kode_brng")
            ->leftJoin('ipsrssuplier', "{$db}.ipsrs_minmax_stok_barang.kode_suplier", '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1')
            ->where('ipsrsbarang.stok', '<=', DB::raw("ifnull({$db}.ipsrs_minmax_stok_barang.stok_min, 0)"))
            ->when(!$saranOrderNol, fn (Builder $query) => $query->whereRaw("ifnull(ifnull({$db}.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') > 0"));
    }
}
