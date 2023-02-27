<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        return $query->selectRaw("
            count(penjualan.nota_jual) jumlah,
            month(penjualan.tgl_jual) bulan
        ")
            ->where('status', 'Sudah Dibayar')
            ->whereBetween('tgl_jual', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(penjualan.tgl_jual)');
    }

    public function scopePendapatanWalkIn(Builder $query, string $year = '2022'): Builder
    {
        return $query->selectRaw("
            round(sum(dj.total + penjualan.ppn)) jumlah,
            month(penjualan.tgl_jual) bulan
        ")
            ->leftJoin(DB::raw("(
                select sum(detailjual.subtotal) total, detailjual.nota_jual
                from detailjual
                group by detailjual.nota_jual
            ) dj"), 'penjualan.nota_jual', '=', 'dj.nota_jual')
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
