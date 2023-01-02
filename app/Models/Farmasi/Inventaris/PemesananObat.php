<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PemesananObat extends Model
{
    protected $primaryKey = 'no_faktur';

    protected $keyType = 'string';

    protected $table = 'pemesanan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePembelianFarmasi(Builder $query, string $year = '2022'): Builder
    {
        return $query->selectRaw("
            round(sum(detailpesan.total)) jumlah,
            month(pemesanan.tgl_pesan) bulan
        ")
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->whereBetween('pemesanan.tgl_pesan', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw("month(pemesanan.tgl_pesan)");
    }

    public static function totalPembelianDariFarmasi(string $year = '2022'): array
    {
        $data = (new static)::pembelianFarmasi($year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }
}
