<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReturObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_retur_jual';

    protected $keyType = 'string';

    protected $table = 'returjual';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeReturObatPasien(Builder $query, string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            round(sum(detreturjual.subtotal)) jumlah,
            month(returjual.tgl_retur) bulan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'float', 'bulan' => 'int'])
            ->join('detreturjual', 'returjual.no_retur_jual', '=', 'detreturjual.no_retur_jual')
            ->whereBetween('returjual.tgl_retur', ["{$year}-01-01", "{$year}-12-31"])
            ->groupByRaw('month(returjual.tgl_retur)');
    }

    public static function totalReturObat(string $year = '2022'): array
    {
        $data = static::returObatPasien($year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        

        $sqlSelect = <<<SQL
            left(returjual.no_retur_jual, 17) as no_rawat,
            detreturjual.kode_brng as kd_jenis_prw,
            databarang.nama_brng as nm_perawatan,
            detreturjual.h_retur as biaya_rawat,
            0 as embalase,
            0 as tuslah,
            0 as diskon,
            0 as tambahan,
            sum(detreturjual.jml_retur) as jml,
            (sum(detreturjual.subtotal) * -1) as subtotal,
            'Retur Obat' as kategori,
            '300000' as kode_barang_jasa
            SQL;

        return $query;
    }
}
