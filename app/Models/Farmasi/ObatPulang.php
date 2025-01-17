<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ObatPulang extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'resep_pulang';

    protected $primaryKey = false;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        $sqlSelect = <<<'SQL'
            resep_pulang.no_rawat,
            resep_pulang.kode_brng as kd_jenis_prw,
            databarang.nama_brng as nm_perawatan,
            resep_pulang.harga as biaya_rawat,
            0 as embalase,
            0 as tuslah,
            0 as diskon,
            0 as tambahan,
            sum(resep_pulang.jml_barang) as jml,
            sum(resep_pulang.total) as subtotal,
            'Obat Pulang' as kategori,
            '300000' as kode_barang_jasa
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'resep_pulang.kode_brng', '=', 'databarang.kode_brng')
            ->whereIn('resep_pulang.no_rawat', $noRawat)
            ->groupBy(['resep_pulang.no_rawat', 'resep_pulang.kode_brng', 'databarang.nama_brng', 'resep_pulang.harga']);
    }
}
