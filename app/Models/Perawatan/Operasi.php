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

    public function scopeItemFakturPajak(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $kodePJ = 'BPJ'): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $tahun = substr($tglAwal, 0, 4);

        $noRawat = RegistrasiPasien::query()->filterFakturPajak($tglAwal, $tglAkhir, $kodePJ);

        $sqlSelect = <<<'SQL'
            operasi.no_rawat,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            paket_operasi.nm_perawatan as nama_barang_jasa,
            'UM.0033' as nama_satuan_ukur,
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
            0 as tambahan,
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
            0 as ppn_persen,
            0 as ppn_nominal,
            operasi.kode_paket as kd_jenis_prw,
            'Operasi' as kategori,
            operasi.status as status_lanjut,
            14 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('paket_operasi', 'operasi.kode_paket', '=', 'paket_operasi.kode_paket')
            ->whereIn('operasi.no_rawat', $noRawat);
    }
}
