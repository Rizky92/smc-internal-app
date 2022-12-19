<?php

namespace App\Models\Logistik;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class BarangNonMedis extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrsbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function stokMinmax(): HasOne
    {
        return $this->hasOne(MinmaxStokBarangNonMedis::class, 'kode_brng', 'kode_brng');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'kode_sat', 'kode_sat');
    }

    public function jenisBarang(): BelongsTo
    {
        return $this->belongsTo(JenisBarangNonMedis::class, 'jenis', 'kd_jenis');
    }

    public function scopeDenganMinmax(Builder $query, string $cari = '', bool $export = false): Builder
    {
        $selectQuery = "
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            IFNULL(ipsrssuplier.kode_suplier, '-') kode_supplier,
            IFNULL(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            IF(ipsrsbarang.stok <= IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0), IFNULL(IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0)) - ipsrsbarang.stok, 0), 0) saran_order,
            ipsrsbarang.harga,
            IF(ipsrsbarang.stok <= IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0), ipsrsbarang.harga * (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok), 0) total_harga
        ";

        if ($export) {
            $selectQuery = str_replace("IFNULL(ipsrssuplier.kode_suplier, '-') kode_supplier,", '', $selectQuery);
        }

        return $query->selectRaw($selectQuery)
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('smc.ipsrs_minmax_stok_barang', 'ipsrsbarang.kode_brng', '=', 'smc.ipsrs_minmax_stok_barang.kode_brng')
            ->leftJoin('ipsrssuplier', 'smc.ipsrs_minmax_stok_barang.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('ipsrsbarang.kode_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrsbarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrssuplier.kode_suplier', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrssuplier.nama_suplier', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrsjenisbarang.nm_jenis', 'LIKE', "%{$cari}%")
                        ->orWhere('kodesatuan.satuan', 'LIKE', "%{$cari}%");
                });
            });
    }

    public function scopeDaruratStok(Builder $query, string $cari = '', bool $saranOrderNol = true): Builder
    {
        return $query->selectRaw("
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            IFNULL(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            IFNULL(IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0') saran_order,
            ipsrsbarang.harga,
            (ipsrsbarang.harga * (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok)) total_harga
        ")
            ->leftJoin('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->leftJoin('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('smc.ipsrs_minmax_stok_barang', 'ipsrsbarang.kode_brng', '=', 'smc.ipsrs_minmax_stok_barang.kode_brng')
            ->leftJoin('ipsrssuplier', 'smc.ipsrs_minmax_stok_barang.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->where('ipsrsbarang.status', '1')
            ->where('ipsrsbarang.stok', '<=', DB::raw('IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0)'))
            ->when(!$saranOrderNol, function (Builder $query) {
                return $query->where(DB::raw("IFNULL(IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok, '0')"), '>', 0);
            })
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('ipsrsbarang.kode_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrsbarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrssuplier.kode_suplier', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrssuplier.nama_suplier', 'LIKE', "%{$cari}%")
                        ->orWhere('ipsrsjenisbarang.nm_jenis', 'LIKE', "%{$cari}%")
                        ->orWhere('kodesatuan.satuan', 'LIKE', "%{$cari}%");
                });
            });
    }
}
