<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Operasi extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'operasi';

    protected $primaryKey = false;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, array $noRawat = []): Builder
    {
        if (empty($noRawat)) {
            return $query;
        }

        $sqlSelect = <<<'SQL'
            operasi.no_rawat, operasi.kode_paket as kd_jenis_prw, paket_operasi.nm_perawatan, (
                operasi.biayaoperator1 + operasi.biayaoperator2 + operasi.biayaoperator3 +
                operasi.biayaasisten_operator1 + operasi.biayaasisten_operator2 + operasi.biayaasisten_operator3 +
                operasi.biayainstrumen + operasi.biayadokter_anak + operasi.biayaperawaat_resusitas +
                operasi.biayadokter_anestesi + operasi.biayaasisten_anestesi + operasi.biayaasisten_anestesi2 +
                operasi.biayabidan + operasi.biayabidan2 + operasi.biayabidan3 + operasi.biayaperawat_luar +
                operasi.biayaalat + operasi.biayasewaok + operasi.akomodasi + operasi.bagian_rs +
                operasi.biaya_omloop + operasi.biaya_omloop2 + operasi.biaya_omloop3 + operasi.biaya_omloop4 + operasi.biaya_omloop5 +
                operasi.biayasarpras + operasi.biaya_dokter_pjanak + operasi.biaya_dokter_umum
            ) as biaya_rawat, 0 as embalase, 0 as tuslah, 0 as diskon, 0 as tambahan, 1 as jml, (
                operasi.biayaoperator1 + operasi.biayaoperator2 + operasi.biayaoperator3 +
                operasi.biayaasisten_operator1 + operasi.biayaasisten_operator2 + operasi.biayaasisten_operator3 +
                operasi.biayainstrumen + operasi.biayadokter_anak + operasi.biayaperawaat_resusitas +
                operasi.biayadokter_anestesi + operasi.biayaasisten_anestesi + operasi.biayaasisten_anestesi2 +
                operasi.biayabidan + operasi.biayabidan2 + operasi.biayabidan3 + operasi.biayaperawat_luar +
                operasi.biayaalat + operasi.biayasewaok + operasi.akomodasi + operasi.bagian_rs +
                operasi.biaya_omloop + operasi.biaya_omloop2 + operasi.biaya_omloop3 + operasi.biaya_omloop4 + operasi.biaya_omloop5 +
                operasi.biayasarpras + operasi.biaya_dokter_pjanak + operasi.biaya_dokter_umum
            ) as subtotal, 'Operasi' as kategori
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('paket_operasi', 'operasi.kode_paket', '=', 'paket_operasi.kode_paket')
            ->whereIn('operasi.no_rawat', $noRawat);
    }
}
