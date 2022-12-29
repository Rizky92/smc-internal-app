<?php

namespace App\Models\Farmasi\Inventaris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GudangObat extends Model
{
    protected $primaryKey = null;

    protected $keyType = null;

    protected $table = 'gudangbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeStokPerRuangan(Builder $query, string $periodeAwal = '', string $periodeAkhir = '', string $cari = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query;
    }
}
