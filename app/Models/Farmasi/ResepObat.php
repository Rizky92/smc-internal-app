<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResepObat extends Model
{
    use Searchable, Sortable;

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePenggunaanObatPerDokter(Builder $query, string $periodeAwal = '', string $periodeAkhir = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query
            ->selectRaw("
                resep_obat.no_resep,
                resep_obat.tgl_perawatan,
                resep_obat.jam,
                databarang.nama_brng,
                resep_dokter.jml,
                dokter.nm_dokter,
                resep_obat.status,
                poliklinik.nm_poli
            ")
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('detail_pemberian_obat', 'reg_periksa.no_rawat', '=', 'detail_pemberian_obat.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereRaw('detail_pemberian_obat.tgl_perawatan = resep_obat.tgl_perawatan')
            ->whereRaw('detail_pemberian_obat.jam = resep_obat.jam')
            ->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir]);
    }

    public function scopeKunjunganFarmasi(Builder $query, string $periodeAwal = '', string $periodeAkhir = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endofMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            resep_obat.no_rawat,
            resep_obat.no_resep,
            pasien.nm_pasien,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) umur,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            dokter_peresep.nm_dokter nm_dokter_peresep,
            dokter_poli.nm_dokter nm_dokter_poli,
            reg_periksa.status_lanjut,
            poliklinik.nm_poli
        ")
            ->leftJoin('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin(DB::raw('dokter dokter_poli'), 'reg_periksa.kd_dokter', '=', 'dokter_poli.kd_dokter')
            ->leftJoin(DB::raw('dokter dokter_peresep'), 'resep_obat.kd_dokter', '=', 'dokter_peresep.kd_dokter')
            ->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir]);
    }

    public function scopeKunjunganResepPasien(Builder $query, string $periodeAwal = '', string $periodeAkhir = '', string $jenisPerawatan = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query->selectRaw("
            reg_periksa.no_rawat,
            resep_obat.no_resep,
            pasien.nm_pasien,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            reg_periksa.status_lanjut,
            round(sum(resep_dokter.jml * databarang.h_beli)) total
        ")
            ->leftJoin('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir])
            ->when(!empty($jenisPerawatan), fn (Builder $query) => $query->where('reg_periksa.status_lanjut', $jenisPerawatan))
            ->groupBy([
                'reg_periksa.no_rawat',
                'resep_obat.no_resep',
                'pasien.nm_pasien',
                'resep_obat.tgl_perawatan',
                'resep_obat.jam',
                'reg_periksa.status_lanjut',
            ]);
    }

    public function scopeKunjunganPasien(Builder $query, string $jenisPerawatan = '', string $year = '2022'): Builder
    {
        return $query->selectRaw("
            count(resep_obat.no_resep) jumlah,
            month(resep_obat.tgl_perawatan) bulan
        ")
            ->leftJoin('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('resep_obat.tgl_perawatan', ["{$year}-01-01", "{$year}-12-31"])
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                switch (Str::lower($jenisPerawatan)) {
                    case 'ralan':
                        return $query->where('resep_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('resep_obat.status', 'Ranap');
                    case 'igd':
                        return $query->where('resep_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            })
            ->groupByRaw('month(resep_obat.tgl_perawatan)');
    }

    public static function kunjunganPasienRalan(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('ralan', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function kunjunganPasienRanap(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('ranap', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function kunjunganPasienIGD(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('IGD', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
