<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRanapPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_inap_pr';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        $sqlSelect = <<<'SQL'
            rawat_inap_pr.no_rawat,
            rawat_inap_pr.kd_jenis_prw,
            jns_perawatan_inap.nm_perawatan,
            rawat_inap_pr.biaya_rawat,
            0 as embalase,
            0 as tuslah,
            0 as diskon,
            0 as tambahan,
            count(*) as jml,
            (rawat_inap_pr.biaya_rawat * count(*)) as subtotal,
            'Tindakan Ranap Pr' as kategori
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->whereIn('rawat_inap_pr.no_rawat', $noRawat)
            ->groupBy(['rawat_inap_pr.no_rawat', 'rawat_inap_pr.kd_jenis_prw', 'jns_perawatan_inap.nm_perawatan', 'rawat_inap_pr.biaya_rawat']);
    }
}
