<?php

namespace App\Models\Nonmedis;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class BarangNonmedis extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'ipsrsbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function stokMinmax(): HasOne
    {
        return $this->hasOne(MinmaxBarangNonmedis::class, 'kode_brng', 'kode_brng');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'kode_sat', 'kode_sat');
    }

    public function jenisBarang(): BelongsTo
    {
        return $this->belongsTo(JenisBarangNonmedis::class, 'jenis', 'kd_jenis');
    }

    public function scopeDaruratStok(Builder $query): Builder
    {
        return $query->selectRaw("
            ipsrsbarang.kode_brng,
            ipsrsbarang.nama_brng,
            IFNULL(ipsrssuplier.kode_suplier, '-') kode_supplier,
            IFNULL(ipsrssuplier.nama_suplier, '-') nama_supplier,
            ipsrsjenisbarang.nm_jenis jenis,
            kodesatuan.satuan,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0) stokmin,
            IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) stokmax,
            ipsrsbarang.stok,
            (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok) saran_order,
            ipsrsbarang.harga,
            (ipsrsbarang.harga * (IFNULL(smc.ipsrs_minmax_stok_barang.stok_max, 0) - ipsrsbarang.stok)) total_harga
        ")
            ->join('ipsrsjenisbarang', 'ipsrsbarang.jenis', '=', 'ipsrsjenisbarang.kd_jenis')
            ->join('kodesatuan', 'ipsrsbarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('smc.ipsrs_minmax_stok_barang', 'ipsrsbarang.kode_brng', '=', 'smc.ipsrs_minmax_stok_barang.kode_brng')
            ->leftJoin('ipsrssuplier', 'smc.ipsrs_minmax_stok_barang.kode_suplier', '=', 'ipsrssuplier.kode_suplier')
            ->where(DB::raw('ipsrsbarang.status'), '1')
            ->where(DB::raw('ipsrsbarang.stok'), '<=', DB::raw('IFNULL(smc.ipsrs_minmax_stok_barang.stok_min, 0)'));
    }
}
