<?php

namespace App\Models\Perawatan;

use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Registrasi extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjunganRalan(Builder $query): Builder
    {
        return $query->selectRaw("
            'Rawat Jalan' kategori,
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->where('reg_periksa.status_lanjut', '=', 'Ralan')
            ->where('poliklinik.kd_poli', '!=', 'IGDK')
            ->groupBy([
                DB::raw('reg_periksa.status_lanjut'),
                DB::raw("DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y')"),
            ]);
    }

    public function scopeKunjunganRanap(Builder $query): Builder
    {
        return $query->selectRaw("
            'Rawat Inap' kategori,
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->where('reg_periksa.status_lanjut', '=', 'Ranap')
            ->where('poliklinik.kd_poli', '!=', 'IGDK')
            ->groupBy([
                DB::raw('reg_periksa.status_lanjut'),
                DB::raw("DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y')"),
            ]);
    }

    public function scopeKunjunganIGD(Builder $query): Builder
    {
        return $query->selectRaw("
            'IGD' kategori,
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->where('poliklinik.kd_poli', '=', 'IGDK')
            ->groupByRaw("DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y')");
    }

    public function scopeKunjunganTotal(Builder $query): Builder
    {
        return $query->selectRaw("
            'TOTAL' kategori,
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
            ->where('reg_periksa.stts', '!=', 'Batal')
            ->groupByRaw("DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y')");
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function rawatInap(): BelongsToMany
    {
        return $this->belongsToMany(Kamar::class, 'kamar_inap', 'no_rawat', 'kd_kamar')
            ->withPivot(RawatInap::$pivotColumns)
            ->using(RawatInap::class);
    }

    public function diagnosa(): BelongsToMany
    {
        return $this->belongsToMany(Penyakit::class, 'diagnosa_pasien', 'no_rawat', 'kd_penyakit');
    }

    public function tindakanRalanDokter(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_dr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanDokter::$pivotColumns)
            ->using(TindakanRalanDokter::class);
    }

    public function tindakanRalanPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_pr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanPerawat::$pivotColumns)
            ->using(TindakanRalanPerawat::class);
    }

    public function tindakanRalanDokterPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_drpr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanDokterPerawat::$pivotColumns)
            ->using(TindakanRalanDokterPerawat::class);
    }

    public function tindakanRanapDokter(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_dr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapDokter::$pivotColumns)
            ->using(TindakanRanapDokter::class);
    }

    public function tindakanRanapPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_pr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapPerawat::$pivotColumns)
            ->using(TindakanRanapPerawat::class);
    }

    public function tindakanRanapDokterPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_drpr', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapDokterPerawat::$pivotColumns)
            ->using(TindakanRanapDokterPerawat::class);
    }

    public static function totalKunjunganRalan()
    {
        return (new static)->kunjunganRalan()
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganRanap()
    {
        return (new static)->kunjunganRanap()
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganIGD()
    {
        return (new static)->kunjunganIGD()
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
