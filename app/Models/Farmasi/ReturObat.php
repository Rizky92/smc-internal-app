<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReturObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_retur_jual';

    protected $keyType = 'string';

    protected $table = 'returjual';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeReturObatPasien(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detreturjual.subtotal)) jumlah,
            month(returjual.tgl_retur) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->join('detreturjual', 'returjual.no_retur_jual', '=', 'detreturjual.no_retur_jual')
            ->whereBetween('returjual.tgl_retur', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(returjual.tgl_retur)');
    }

    public static function totalReturObat(string $year = '2022'): array
    {
        $data = static::returObatPasien($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
