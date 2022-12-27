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

    public function scopePembelianFarmasi(Builder $query): Builder
    {
        return $query->selectRaw("
            round(sum(detailpesan.total)) jumlah,
            date_format(pemesanan.tgl_pesan, '%m-%Y') bulan
        ")
            ->join('detailpesan', 'pemesanan.no_faktur', '=', 'detailpesan.no_faktur')
            ->whereBetween('pemesanan.tgl_pesan', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw("date_format(pemesanan.tgl_pesan, '%m-%Y')");
    }

    public static function totalPembelianDariFarmasi(): array
    {
        return (new static)->pembelianFarmasi()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
