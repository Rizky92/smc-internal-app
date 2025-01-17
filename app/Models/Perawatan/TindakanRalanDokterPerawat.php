<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TindakanRalanDokterPerawat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'rawat_jl_drpr';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeItemFakturPajak(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $tahun = substr($tglAwal, 0, 4);

        $noRawat = RegistrasiPasien::query()->filterFakturPajak($tglAwal, $tglAkhir);

        $sqlSelect = <<<'SQL'
            rawat_jl_drpr.no_rawat,
            'B' as jenis_barang_jasa,
            '250100' as kode_barang_jasa,
            jns_perawatan.nm_perawatan as nama_barang_jasa,
            'UM.0033' as nama_satuan_ukur,
            rawat_jl_drpr.biaya_rawat as harga_satuan,
            count(*) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            0 as tambahan,
            (rawat_jl_drpr.biaya_rawat * count(*)) as dpp,
            0 as ppn_persen,
            0 as ppn_nominal,
            rawat_jl_drpr.kd_jenis_prw,
            'Tindakan Ralan DrPr' as kategori,
            'Ralan' as status_lanjut
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->whereIn('rawat_jl_drpr.no_rawat', $noRawat)
            ->groupBy(['rawat_jl_drpr.no_rawat', 'rawat_jl_drpr.kd_jenis_prw', 'jns_perawatan.nm_perawatan', 'rawat_jl_drpr.biaya_rawat']);
    }
}
