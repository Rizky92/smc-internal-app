<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Builder;

class ObatPulang extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'resep_pulang';

    protected $primaryKey = false;

    protected $keyType = null;

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

        $noRawat = RegistrasiPasien::query()->filterFakturPajak($tglAwal, $tglAkhir, $kodePJ);

        $sqlSelect = <<<'SQL'
            resep_pulang.no_rawat,
            'A' as jenis_barang_jasa,
            '300000' as kode_barang_jasa,
            databarang.nama_brng as nama_barang_jasa,
            databarang.kode_sat as nama_satuan_ukur,
            resep_pulang.harga as harga_satuan,
            sum(resep_pulang.jml_barang) as jumlah_barang_jasa,
            0 as diskon_persen,
            0 as diskon_nominal,
            0 as tambahan,
            sum(resep_pulang.total) as dpp,
            0 as ppn_persen,
            0 as ppn_nominal,
            resep_pulang.kode_brng as kd_jenis_prw,
            'Obat Pulang' as kategori,
            'Ranap' as status_lanjut,
            13 as urutan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('databarang', 'resep_pulang.kode_brng', '=', 'databarang.kode_brng')
            ->whereIn('resep_pulang.no_rawat', $noRawat)
            ->groupBy(['resep_pulang.no_rawat', 'resep_pulang.kode_brng', 'databarang.nama_brng', 'resep_pulang.harga']);
    }
}
