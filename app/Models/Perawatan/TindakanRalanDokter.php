<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRalanDokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_dr';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_jl_dr.no_rawat,
            rawat_jl_dr.kd_jenis_prw,
            jns_perawatan.nm_perawatan,
            rawat_jl_dr.biaya_rawat,
            0 as embalase,
            0 as tuslah,
            0 as diskon,
            0 as tambahan,
            count(*) as jml,
            (rawat_jl_dr.biaya_rawat * count(*)) as subtotal,
            'Tindakan Ralan Dr' as kategori
            SQL;
            
        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->whereIn('rawat_jl_dr.no_rawat', $noRawat)
            ->groupBy(['rawat_jl_dr.no_rawat', 'rawat_jl_dr.kd_jenis_prw', 'jns_perawatan.nm_perawatan', 'rawat_jl_dr.biaya_rawat']);
    }
}
