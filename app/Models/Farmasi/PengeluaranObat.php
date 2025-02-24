<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PengeluaranObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_keluar';

    protected $keyType = 'string';

    protected $table = 'pengeluaran_obat_bhp';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeStokKeluarMedis(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detail_pengeluaran_obat_bhp.total)) jumlah,
            month(pengeluaran_obat_bhp.tanggal) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->leftJoin('detail_pengeluaran_obat_bhp', 'pengeluaran_obat_bhp.no_keluar', '=', 'detail_pengeluaran_obat_bhp.no_keluar')
            ->join('databarang', 'detail_pengeluaran_obat_bhp.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('pengeluaran_obat_bhp.tanggal', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(pengeluaran_obat_bhp.tanggal)');
    }

    public static function stokPengeluaranMedisFarmasi(string $year = '2022'): array
    {
        $data = static::stokKeluarMedis($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
