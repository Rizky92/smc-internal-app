<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AntreanLoketFarmasi extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'antriloketfarmasi_smc';

    protected $primaryKey = ['tanggal', 'nomor'];

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function getJamPanggilSingkatAttribute(): ?string
    {
        return $this->jam_panggil ? substr($this->jam_panggil, 0, 5) : null;
    }

    /**
     * @psalm-return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function scopeTerakhirDipanggilHariIni(Builder $query): Builder
    {
        return $query
            ->where('tanggal', now()->toDateString())
            ->whereNotNull('jam_panggil')
            ->orderByDesc('jam_panggil');
    }
}
