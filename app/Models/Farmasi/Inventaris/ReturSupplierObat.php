<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReturSupplierObat extends Model
{
    protected $primaryKey = 'no_retur_beli';

    protected $keyType = 'string';

    protected $table = 'returbeli';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeReturKeSupplier(Builder $query, string $year = '2022'): Builder
    {
        return $query->selectRaw("
            ceil(sum(detreturbeli.total)) jumlah,
            month(returbeli.tgl_retur) total
        ")
            ->leftJoin('detreturbeli', 'returbeli.no_retur_beli', '=', 'detreturbeli.no_retur_beli')
            ->whereBetween('returbeli.tgl_retur', ["{$year}-01-01", "{$year}-12-31"])
            ->whereIn('returbeli.kd_bangsal', ['IFA', 'IFG', 'AP'])
            ->groupByRaw('month(returbeli.tgl_retur)');
    }

    public static function totalBarangRetur(string $year = '2022'): array
    {
        $data = (new static)::returKeSupplier($year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }
}
