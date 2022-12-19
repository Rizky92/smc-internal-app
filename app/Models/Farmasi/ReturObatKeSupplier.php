<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReturObatKeSupplier extends Model
{
    protected $primaryKey = 'no_retur_beli';

    protected $keyType = 'string';

    protected $table = 'returbeli';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeReturKeSupplier(Builder $query): Builder
    {
        return $query->selectRaw("
            CEIL(SUM(detreturbeli.total)) jumlah,
            DATE_FORMAT(returbeli.tgl_retur, '%m-%Y') total
        ")
            ->leftJoin('detreturbeli', 'returbeli.no_retur_beli', '=', 'detreturbeli.no_retur_beli')
            ->whereRaw('YEAR(returbeli.tgl_retur) = ?', now()->format('Y'))
            ->whereIn('returbeli.kd_bangsal', ['IFA', 'IFG', 'AP'])
            ->groupByRaw("DATE_FORMAT(returbeli.tgl_retur, '%m-%Y')");
    }

    public static function totalBarangRetur(): array
    {
        return (new static)->returKeSupplier()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
