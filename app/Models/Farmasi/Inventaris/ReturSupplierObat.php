<?php

namespace App\Models\Farmasi\Inventaris;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReturSupplierObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_retur_beli';

    protected $keyType = 'string';

    protected $table = 'returbeli';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeReturKeSupplier(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            ceil(sum(detreturbeli.total)) jumlah,
            month(returbeli.tgl_retur) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->leftJoin('detreturbeli', 'returbeli.no_retur_beli', '=', 'detreturbeli.no_retur_beli')
            ->whereBetween('returbeli.tgl_retur', ["{$year}-01-01", "{$year}-12-31"])
            ->whereIn('returbeli.kd_bangsal', ['IFA', 'IFG', 'AP'])
            ->groupByRaw('month(returbeli.tgl_retur)');
    }

    public static function totalBarangRetur(string $year = '2022'): array
    {
        $data = static::returKeSupplier($year)->pluck('jumlah', 'bulan')->map(fn ($v) => floatval($v));

        return map_bulan($data);
    }
}
