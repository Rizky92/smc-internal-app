<?php

namespace App\Models\Farmasi;

use App\Models\Dokter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResepObat extends Model
{
    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    public function scopePenggunaanObatPerDokter(
        Builder $query,
        string $dateMin = '',
        string $dateMax = '',
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
            ->when(!empty($dateMin) || !empty($dateMax), function (Builder $query) use ($dateMin, $dateMax) {
                return $query->whereBetween('resep_obat.tgl_perawatan', [$dateMin, $dateMax]);
            })
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->whereNotNull('resep_dokter.kode_brng')
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query->where('resep_obat.no_resep', 'LIKE', "%{$cari}%")
                        ->orWhere('databarang.nama_brng', 'LIKE', "%{$cari}%")
                        ->orWhere('dokter.nm_dokter', 'LIKE', "%{$cari}%")
                        ->orWhere('poliklinik.nm_poli', 'LIKE', "%{$cari}%")
                        ->orWhere('poliklinik.nm_poli', 'LIKE', "%{$cari}%");
                });
            })
            ->orderBy('resep_obat.no_resep');
    }

    public function scopeKunjunganPasien(Builder $query, $poli = ''): Builder
    {
        return $query->selectRaw("
            COUNT(resep_obat.no_resep) jumlah,
            DATE_FORMAT(resep_obat.tgl_perawatan, '%m-%Y') bulan
        ")
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereNotIn('reg_periksa.stts', ['Belum', 'Batal'])
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->when(!empty($poli), function (Builder $query) use ($poli) {
                switch (Str::title($poli)) {
                    case 'Ralan':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'Ranap':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ranap')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'Igd':
                        return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
                    case 'Walkin':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereIn('reg_periksa.kd_poli', ['U0056', 'U0057']);
                }
            })
            ->whereBetween('resep_obat.tgl_perawatan', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw('DATE_FORMAT(resep_obat.tgl_perawatan, "%m-%Y")');
    }

    public function scopePendapatanObat(Builder $query, $poli = ''): Builder
    {
        return $query->selectRaw("
            CEIL(SUM(resep_dokter.jml * databarang.h_beli)) jumlah,
            DATE_FORMAT(resep_obat.tgl_perawatan, '%m-%Y') bulan
        ")
            ->join('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
            ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereNotIn('reg_periksa.stts', ['Belum', 'Batal'])
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->where('databarang.kode_kategori', 'NOT LIKE', "3.%")
            ->when(!empty($poli), function (Builder $query) use ($poli) {
                switch (Str::title($poli)) {
                    case 'Ralan':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'Ranap':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ranap')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'Igd':
                        return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
                    case 'Walkin':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereIn('reg_periksa.kd_poli', ['U0056', 'U0057']);
                }
            })
            ->whereBetween('resep_obat.tgl_perawatan', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
            ->groupByRaw('DATE_FORMAT(resep_obat.tgl_perawatan, "%m-%Y")');
    }

    // public function scopePendapatanObatSelainFarmasi(Builder $query): Builder
    // {
    //     return $query->selectRaw("
    //         CEIL(SUM(resep_dokter.jml * databarang.h_beli)) jumlah,
    //         DATE_FORMAT(resep_obat.tgl_perawatan, '%m-%Y') bulan
    //     ")
    //         ->join('resep_dokter', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
    //         ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
    //         ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
    //         ->whereNotIn('reg_periksa.stts', ['Belum', 'Batal'])
    //         ->where('reg_periksa.status_bayar', 'Sudah Bayar')
    //         ->when(!empty($poli), function (Builder $query) use ($poli) {
    //             switch (Str::title($poli)) {
    //                 case 'Ralan':
    //                     return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
    //                         ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
    //                 case 'Ranap':
    //                     return $query->where('reg_periksa.status_lanjut', '=', 'Ranap')
    //                         ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
    //                 case 'Igd':
    //                     return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
    //                 case 'Walkin':
    //                     return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
    //                         ->whereIn('reg_periksa.kd_poli', ['U0056', 'U0057']);
    //             }
    //         })
    //         ->whereBetween('resep_obat.tgl_perawatan', [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')])
    //         ->groupByRaw('DATE_FORMAT(resep_obat.tgl_perawatan, "%m-%Y")');
    // }

    public function dokterPeresep()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function obat()
    {
        return $this->belongsToMany(DataBarang::class, 'resep_dokter', 'no_resep', 'kode_brng')
            ->withPivot(ResepDokter::$pivotColumns)
            ->using(ResepDokter::class);
    }

    public static function kunjunganPasienRalan()
    {
        return (new static)->kunjunganPasien('Ralan')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function kunjunganPasienRanap()
    {
        return (new static)->kunjunganPasien('Ranap')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function kunjunganPasienIGD()
    {
        return (new static)->kunjunganPasien('IGD')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function kunjunganPasienWalkIn()
    {
        return (new static)->kunjunganPasien('Walkin')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatRalan()
    {
        return (new static)->pendapatanObat('Ralan')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatRanap()
    {
        return (new static)->pendapatanObat('Ranap')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatIGD()
    {
        return (new static)->pendapatanObat('Igd')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function pendapatanObatWalkIn()
    {
        return (new static)->pendapatanObat('Walkin')->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
