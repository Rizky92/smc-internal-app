<?php

namespace App\Models\Farmasi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PengeluaranStokObat extends Model
{
    protected $primaryKey = 'no_keluar';

    protected $keyType = 'string';

    protected $table = 'pengeluaran_obat_bhp';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeStokKeluarMedis(Builder $query): Builder
    {
        return $query->selectRaw("
            ROUND(SUM(detail_pengeluaran_obat_bhp.total)) jumlah,
            DATE_FORMAT(pengeluaran_obat_bhp.tanggal, '%m-%Y') bulan
        ")
            ->leftJoin('detail_pengeluaran_obat_bhp', 'pengeluaran_obat_bhp.no_keluar', '=', 'detail_pengeluaran_obat_bhp.no_keluar')
            ->join('databarang', 'detail_pengeluaran_obat_bhp.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('pengeluaran_obat_bhp.tanggal', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw('DATE_FORMAT(pengeluaran_obat_bhp.tanggal, "%m-%Y")');
    }

    public static function stokPengeluaranMedisFarmasi(): array
    {
        return (new static)->stokKeluarMedis()->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
