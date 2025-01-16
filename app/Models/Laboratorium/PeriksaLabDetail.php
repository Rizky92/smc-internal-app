<?php

namespace App\Models\Laboratorium;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PeriksaLabDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'detail_periksa_lab';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        if (empty($noRawat)) {
            return $query;
        }

        $sqlSelect = <<<SQL
            detail_periksa_lab.no_rawat,
            concat_ws('-', detail_periksa_lab.kd_jenis_prw, detail_periksa_lab.id_template) as kd_jenis_prw,
            template_laboratorium.Pemeriksaan as nm_perawatan,
            detail_periksa_lab.biaya_item as biaya_rawat,
            0 as embalase,
            0 as tuslah,
            0 as diskon,
            0 as tambahan,
            count(*) as jml,
            (detail_periksa_lab.biaya_item * count(*)) as subtotal,
            'Laborat Detail' as kategori
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
            ->whereIn('detail_periksa_lab.no_rawat', $noRawat)
            ->groupBy(['detail_periksa_lab.no_rawat', 'detail_periksa_lab.id_template', 'template_laboratorium.Pemeriksaan', 'detail_periksa_lab.biaya_item']);
    }
}
