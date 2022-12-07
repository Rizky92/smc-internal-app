<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReturJual extends Model
{
    protected $primaryKey = 'no_retur_jual';

    protected $keyType = 'string';

    protected $table = 'returjual';

    public $incrementing = false;

    public $timestamps = false;

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(DataBarang::class, 'detreturjual', 'no_retur_jual', 'kode_brng');
    }

    public function scopeReturObatPasien(Builder $query): Builder
    {
        return $query->selectRaw("
            'RETUR OBAT' kategori,
            ROUND(SUM(detreturjual.subtotal), 2) jumlah,
            DATE_FORMAT(returjual.tgl_retur, '%m-%Y') bulan
        ")
            ->join('detreturjual', 'returjual.no_retur_jual', '=', 'detreturjual.no_retur_jual')
            ->groupByRaw("DATE_FORMAT(returjual.tgl_retur, '%m-%Y')");
    }

    public static function totalReturObat()
    {
        return (new static)->returObatPasien()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
