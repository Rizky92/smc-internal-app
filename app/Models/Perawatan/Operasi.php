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

    public function scopeItemFakturPajak(Builder $query): Builder
    {
        $sqlSelect = <<<'SQL'
            operasi.no_rawat,
            '080' as kode_transaksi,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            paket_operasi.nm_perawatan as nama_barang_jasa,
            '' as nama_satuan_ukur,
            (
                operasi.biayaoperator1 + operasi.biayaoperator2 + operasi.biayaoperator3 +
                operasi.biayaasisten_operator1 + operasi.biayaasisten_operator2 + operasi.biayaasisten_operator3 +
                operasi.biayainstrumen + operasi.biayadokter_anak + operasi.biayaperawaat_resusitas +
                operasi.biayadokter_anestesi + operasi.biayaasisten_anestesi + operasi.biayaasisten_anestesi2 +
                operasi.biayabidan + operasi.biayabidan2 + operasi.biayabidan3 + operasi.biayaperawat_luar +
                operasi.biayaalat + operasi.biayasewaok + operasi.akomodasi + operasi.bagian_rs +
                operasi.biaya_omloop + operasi.biaya_omloop2 + operasi.biaya_omloop3 + operasi.biaya_omloop4 + operasi.biaya_omloop5 +
                operasi.biayasarpras + operasi.biaya_dokter_pjanak + operasi.biaya_dokter_umum
            ) as harga_satuan,
            1 as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            (
                operasi.biayaoperator1 + operasi.biayaoperator2 + operasi.biayaoperator3 +
                operasi.biayaasisten_operator1 + operasi.biayaasisten_operator2 + operasi.biayaasisten_operator3 +
                operasi.biayainstrumen + operasi.biayadokter_anak + operasi.biayaperawaat_resusitas +
                operasi.biayadokter_anestesi + operasi.biayaasisten_anestesi + operasi.biayaasisten_anestesi2 +
                operasi.biayabidan + operasi.biayabidan2 + operasi.biayabidan3 + operasi.biayaperawat_luar +
                operasi.biayaalat + operasi.biayasewaok + operasi.akomodasi + operasi.bagian_rs +
                operasi.biaya_omloop + operasi.biaya_omloop2 + operasi.biaya_omloop3 + operasi.biaya_omloop4 + operasi.biaya_omloop5 +
                operasi.biayasarpras + operasi.biaya_dokter_pjanak + operasi.biaya_dokter_umum
            ) as dpp,
            12 as ppn_persen,
            0 as ppn_nominal,
            operasi.kode_paket as kd_jenis_prw,
            'Operasi' as kategori,
            12 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('paket_operasi', 'operasi.kode_paket', '=', 'paket_operasi.kode_paket')
            ->whereExists(fn ($q) => $q->from('regist_faktur')->whereColumn('regist_faktur.no_rawat', 'operasi.no_rawat'));
    }
}
