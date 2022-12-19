<?php

namespace App\Models\Farmasi;

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
            CEIL(SUM(bayar_pemesanan.besar_bayar)) jumlah,
	        DATE_FORMAT(pemesanan.tgl_faktur, '%m-%Y') bulan
        ")
            ->leftJoin('bayar_pemesanan', 'pemesanan.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->whereRaw('YEAR(pemesanan.tgl_faktur) = ?', now()->format('Y'))
            ->whereIn('pemesanan.kd_bangsal', ['IFA', 'IFG', 'AP'])
            ->groupByRaw("DATE_FORMAT(pemesanan.tgl_faktur, '%m-%Y')");
    }

    public static function totalPembelianDariFarmasi(): array
    {
        return (new static)->pembelianFarmasi()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
