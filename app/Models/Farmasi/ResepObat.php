<?php

namespace App\Models\Farmasi;

use App\Models\Dokter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResepObat extends Model
{
    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    public const RALAN = 'ralan';
    public const RANAP = 'ranap';
    public const IGD = 'igd';

    public function scopePenggunaanObatPerDokter(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $cari = ''
    ): Builder {
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
            ->leftJoin('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
            ->leftJoin('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->when(!empty($periodeAwal) || !empty($periodeAkhir), function (Builder $query) use ($periodeAwal, $periodeAkhir) {
                return $query->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir]);
            })
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->whereNotNull('resep_dokter.kode_brng')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query
                        ->where('resep_obat.no_resep', 'LIKE', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('dokter.nm_dokter', 'LIKE', "%{$cari}%")
                        ->orWhere('resep_obat.status', 'LIKE', "%{$cari}%")
                        ->orWhere('poliklinik.nm_poli', 'LIKE', "%{$cari}%");
                });
            })
            ->orderBy('resep_obat.no_resep');
    }

    public function scopeKunjunganFarmasi(Builder $query, string $periodeAwal = '', string $periodeAkhir = '', string $cari = ''): Builder
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
            ->whereBetween('resep_obat.tgl_perawatan', [$periodeAwal, $periodeAkhir])
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    $cari = Str::lower($cari);

                    return $query
                        ->where('resep_obat.no_rawat', 'like', "%{$cari}%")
                        ->orWhere('resep_obat.no_resep', 'like', "%{$cari}%")
                        ->orWhere('pasien.no_rkm_medis', 'like', "%{$cari}%")
                        ->orWhere('pasien.nm_pasien', 'like', "%{$cari}%")
                        ->orWhere('dokter_peresep.nm_dokter', 'like', "%{$cari}%")
                        ->orWhere('dokter_peresep.kd_dokter', 'like', "%{$cari}%")
                        ->orWhere('dokter_poli.nm_dokter', 'like', "%{$cari}%")
                        ->orWhere('dokter_poli.kd_dokter', 'like', "%{$cari}%")
                        ->orWhere('reg_periksa.status_lanjut', 'like', "%{$cari}%")
                        ->orWhere('poliklinik.nm_poli', 'like', "%{$cari}%");
                });
            });
    }

    public function scopeKunjunganResepPasien(
        Builder $query,
        string $periodeAwal = '',
        string $periodeAkhir = '',
        string $jenisPerawatan = ''
    ): Builder {
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
            ->when(!empty($jenisPerawatan), function (Builder $query) use ($jenisPerawatan) {
                return $query->where('reg_periksa.status_lanjut', $jenisPerawatan);
            })
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
                        return $query->where('resep_obat.status', 'Ranap')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'igd':
                        return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            })
            ->groupByRaw('month(resep_obat.tgl_perawatan)');
    }

    public function dokterPeresep(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function resepDokter(): HasMany
    {
        return $this->hasMany(ResepDokter::class, 'no_resep', 'no_resep');
    }

    public function resepDokterRacikan(): HasMany
    {
        return $this->hasMany(ResepDokterRacikan::class, 'no_resep', 'no_resep');
    }

    public static function kunjunganPasienRalan(string $year = '2022'): array
    {
        $data = (new static)::kunjunganPasien('ralan', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }

    public static function kunjunganPasienRanap(string $year = '2022'): array
    {
        $data = (new static)::kunjunganPasien('ranap', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }

    public static function kunjunganPasienIGD(string $year = '2022'): array
    {
        $data = (new static)::kunjunganPasien('IGD', $year)->get()
            ->mapWithKeys(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->toArray();

        return map_bulan($data);
    }
}
