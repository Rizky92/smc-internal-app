<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PenjualanWalkInObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'nota_jual';

    protected $keyType = 'string';

    protected $table = 'penjualan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganWalkIn(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<SQL
            count(penjualan.nota_jual) jumlah,
            month(penjualan.tgl_jual) bulan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'int', 'bulan' => 'int'])
            ->where('status', 'Sudah Dibayar')
            ->whereBetween('tgl_jual', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(penjualan.tgl_jual)');
    }

    public function scopePendapatanWalkIn(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<SQL
            round(sum(detail_jual.total + penjualan.ppn)) jumlah,
            month(penjualan.tgl_jual) bulan
        SQL;

        $sumDetailJual = DB::connection('mysql_sik')
            ->table('detailjual')
            ->select([DB::raw('sum(detailjual.subtotal) total'), 'detailjual.nota_jual'])
            ->groupBy('detailjual.nota_jual');

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->leftJoinSub($sumDetailJual, 'detail_jual', fn (JoinClause $join) =>
                $join->on('penjualan.nota_jual', '=', 'detail_jual.nota_jual'))
            ->where('penjualan.status', 'Sudah Dibayar')
            ->whereBetween('penjualan.tgl_jual', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(penjualan.tgl_jual)');
    }

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(Obat::class, 'detailjual', 'nota_jual', 'kode_brng');
    }

    public static function totalKunjunganWalkIn(string $year = '2022'): array
    {
        $data = static::kunjunganWalkIn($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function totalPendapatanWalkIn(string $year = '2022'): array
    {
        $data = static::pendapatanWalkIn($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
