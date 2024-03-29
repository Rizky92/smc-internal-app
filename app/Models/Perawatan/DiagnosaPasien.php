<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DiagnosaPasien extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'diagnosa_pasien';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeRalan(Builder $query): Builder
    {
        return $query->where('status', 'ralan');
    }

    public function scopeRanap(Builder $query): Builder
    {
        return $query->where('status', 'ranap');
    }
}
