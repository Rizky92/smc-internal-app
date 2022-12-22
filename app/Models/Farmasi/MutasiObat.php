<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MutasiObat extends Model
{
    protected $primaryKey = 'kode_brng';

    protected $keyType = 'string';

    protected $table = 'mutasibarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeMutasiObatFarmasi(Builder $query): Builder
    {
        return $query->selectRaw("
            ROUND(SUM(mutasibarang.jml * mutasibarang.harga)) jumlah,
            DATE_FORMAT(mutasibarang.tanggal, '%m-%Y') bulan
        ")
            ->whereBetween('mutasibarang.tanggal', [
                now()->startOfYear()->format('Y-m-d'),
                now()->endOfYear()->format('Y-m-d')
            ])
            ->groupByRaw("DATE_FORMAT(mutasibarang.tanggal, '%m-%Y')");
    }

    public static function mutasiObatDariFarmasi(): array
    {
        return (new static)->mutasiObatFarmasi()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
