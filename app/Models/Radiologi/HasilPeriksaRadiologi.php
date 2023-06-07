<?php

namespace App\Models\Radiologi;

use App\Casts\CastAsciiChars;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class HasilPeriksaRadiologi extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = false;

    protected $keyType = false;

    protected $table = 'periksa_radiologi';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'hasil_pemeriksaan' => CastAsciiChars::class,
    ];

    public function scopeLaporanTindakanRadiologi(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            periksa_radiologi.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            penjab.png_jawab,
            petugas.nama nama_petugas,
            periksa_radiologi.tgl_periksa,
            periksa_radiologi.jam,
            periksa_radiologi.dokter_perujuk,
            jns_perawatan_radiologi.kd_jenis_prw,
            jns_perawatan_radiologi.nm_perawatan,
            periksa_radiologi.biaya,
            reg_periksa.status_bayar,
            periksa_radiologi.status,
            periksa_radiologi.kd_dokter,
            dokter.nm_dokter,
            ifnull(LEFT(hasil_radiologi.hasil, 200), '-') hasil_pemeriksaan
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('reg_periksa', 'periksa_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('petugas', 'periksa_radiologi.nip', '=', 'petugas.nip')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dokter', 'periksa_radiologi.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->leftJoin('hasil_radiologi', fn (JoinClause $join) => $join
                ->on('periksa_radiologi.no_rawat', '=', 'hasil_radiologi.no_rawat')
                ->on('periksa_radiologi.tgl_periksa', '=', 'hasil_radiologi.tgl_periksa')
                ->on('periksa_radiologi.jam', '=', 'hasil_radiologi.jam'))
            ->whereBetween('periksa_radiologi.tgl_periksa', [$tglAwal, $tglAkhir])
            ->groupByRaw("concat(
                periksa_radiologi.no_rawat,
                periksa_radiologi.tgl_periksa,
                periksa_radiologi.jam
            )");
    }
}
