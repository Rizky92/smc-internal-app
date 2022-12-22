<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class PenjualanWalkInObat extends Model
{
    protected $primaryKey = 'nota_jual';

    protected $keyType = 'string';

    protected $table = 'penjualan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganWalkIn(Builder $query): Builder
    {
        return $query->selectRaw("
            COUNT(penjualan.nota_jual) jumlah,
            DATE_FORMAT(penjualan.tgl_jual, '%m-%Y') bulan
        ")
            ->where('status', 'Sudah Dibayar')
            ->whereBetween('tgl_jual', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw("DATE_FORMAT(penjualan.tgl_jual, '%m-%Y')");
    }

    public function scopePendapatanWalkIn(Builder $query): Builder
    {
        return $query->selectRaw("
            round(sum(dj.total + penjualan.ppn)) jumlah,
            date_format(penjualan.tgl_jual, '%m-%Y') bulan
        ")
            ->leftJoin(DB::raw("(
                select sum(detailjual.subtotal) total, detailjual.nota_jual
                from detailjual
                group by detailjual.nota_jual
            ) dj"), 'penjualan.nota_jual', '=', 'dj.nota_jual')
            ->where('penjualan.status', 'Sudah Dibayar')
            ->whereBetween('penjualan.tgl_jual', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw("date_format(penjualan.tgl_jual, '%m-%Y')");
    }

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(Obat::class, 'detailjual', 'nota_jual', 'kode_brng');
    }

    public static function totalKunjunganWalkIn(): array
    {
        return (new static)::kunjunganWalkIn()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalPendapatanWalkIn(): array
    {
        return (new static)::pendapatanWalkIn()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
