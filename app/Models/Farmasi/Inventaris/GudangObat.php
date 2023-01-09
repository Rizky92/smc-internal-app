<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GudangObat extends Model
{
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'gudangbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeBangsalYangAda(Builder $query): Builder
    {
        return $query->selectRaw("
            distinct(gudangbarang.kd_bangsal) kd_bangsal,
            bangsal.nm_bangsal
        ")
            ->join('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal');
    }

    public function scopeStokPerRuangan(Builder $query, string $kodeBangsal = '-', string $cari = ''): Builder
    {
        return $query->selectRaw("
            bangsal.nm_bangsal,
            gudangbarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan,
            databarang.h_beli,
            gudangbarang.stok,
            round(databarang.h_beli * if(gudangbarang.stok < 0, 0, gudangbarang.stok)) projeksi_harga
        ")
            ->leftJoin('databarang', 'gudangbarang.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->when($kodeBangsal !== '-', function (Builder $query) use ($kodeBangsal) {
                return $query->where('gudangbarang.kd_bangsal', $kodeBangsal);
            })
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    $cari = Str::lower($cari);

                    return $query->where('databarang.kode_brng', 'like', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'like', "%{$cari}%");
                });
            })
            ->orderBy('databarang.nama_brng');
    }
}
