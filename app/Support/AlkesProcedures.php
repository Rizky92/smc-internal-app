<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AlkesProcedures
{
    protected static array $idsCache = [];

    public static function ids(string $connection, string $table, $nama, array $exclude = []): array
    {
        $key = $connection.'|'.$table.'|'.serialize((array) $nama).'|'.serialize($exclude);

        return self::$idsCache[$key] ??= DB::connection($connection)
            ->table($table)
            ->where(function (Builder $query) use ($nama) {
                $method = 'where';

                foreach ((array) $nama as $term) {
                    $query->{$method}('nm_perawatan', 'like', '%'.$term.'%');
                    $method = 'orWhere';
                }
            })
            ->when($exclude, fn (Builder $query) => $query->where(function (Builder $query) use ($exclude) {
                foreach ($exclude as $item) {
                    $query->where('nm_perawatan', 'not like', '%'.$item.'%');
                }
            }))
            ->pluck('kd_jenis_prw')
            ->all();
    }
}
