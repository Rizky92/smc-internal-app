<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PenjualanWalkIn extends Model
{
    protected $primaryKey = 'nota_jual';

    protected $keyType = 'string';

    protected $table = 'penjualan';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganWalkIn(Builder $query): Builder
    {
        return $query->selectRaw("
            'WALK IN' kategori,
            COUNT(penjualan.nota_jual) jumlah,
            DATE_FORMAT(penjualan.tgl_jual, '%m-%Y') bulan
        ")
            ->where('status', 'Sudah Dibayar')
            ->groupByRaw("DATE_FORMAT(penjualan.tgl_jual, '%m-%Y')");
    }

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(DataBarang::class, 'detailjual', 'nota_jual', 'kode_brng');
    }

    public static function totalKunjunganWalkIn()
    {
        return (new static)->kunjunganWalkIn()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
