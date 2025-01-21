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

        $sqlSelect = <<<'SQL'
            left(returjual.no_nota, 17) as no_rawat,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            detreturjual.h_retur * -1 as harga_satuan,
            sum(detreturjual.jml_retur) * -1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            sum(detreturjual.subtotal) * -1 as dpp,
            0 as ppn_persen,
            0 as ppn_nominal,
            detreturjual.kode_brng as kd_jenis_prw,
            'Pemberian Obat' as kategori,
            'Ranap' as status_lanjut,
            16 as urutan
            SQL;

        return $query;
    }
}
