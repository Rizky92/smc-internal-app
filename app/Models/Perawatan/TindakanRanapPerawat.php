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
            rawat_inap_pr.no_rawat,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan_inap.nm_perawatan as nama_barang_jasa,
            'UM.0033' as nama_satuan_ukur,
            rawat_inap_pr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            0 as tambahan,
            (rawat_inap_pr.biaya_rawat * count(*)) as dpp,
            0 as ppn_persen,
            0 as ppn_nominal,
            rawat_inap_pr.kd_jenis_prw,
            'Tindakan Ranap Pr' as kategori,
            'Ranap' as status_lanjut,
            7 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->whereIn('rawat_inap_pr.no_rawat', $noRawat)
            ->groupBy(['rawat_inap_pr.no_rawat', 'rawat_inap_pr.kd_jenis_prw', 'jns_perawatan_inap.nm_perawatan', 'rawat_inap_pr.biaya_rawat']);
    }
}
