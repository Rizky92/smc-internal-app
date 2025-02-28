<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MutasiObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'mutasibarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeJumlahTransferOrder(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(mutasibarang.jml * mutasibarang.harga)) jumlah,
            month(mutasibarang.tanggal) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->whereBetween('mutasibarang.tanggal', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(mutasibarang.tanggal)');
    }

    public static function transferOrder(string $year = '2022'): array
    {
        $data = static::jumlahTransferOrder($year)->pluck('jumlah', 'bulan')->map(fn ($v) => floatval($v));

        return map_bulan($data);
    }
}
