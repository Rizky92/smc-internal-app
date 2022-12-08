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
use Illuminate\Support\Str;

class Registrasi extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeKunjungan(Builder $query, $poli = ''): Builder
    {
        return $query->selectRaw("
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
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
            ->whereRaw('YEAR(reg_periksa.tgl_registrasi) = ?', now()->format('Y'))
            ->groupByRaw("DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y')");
    }

    public function scopeKunjunganTotal(Builder $query): Builder
    {
        return $query->selectRaw("
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
        return (new static)->kunjungan('Ralan')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganRanap()
    {
        return (new static)->kunjungan('Ranap')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganIGD()
    {
        return (new static)->kunjungan('Igd')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
