<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Registrasi extends Model
{
    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLaporanStatistik(Builder $query)
    {
        return $query->with([
            'pasien',
            'pasien.suku',
            'pasien.kelurahan',
            'pasien.kecamatan',
            'pasien.kabupaten',
            'pasien.provinsi',
        ])
            ->withCount([
                'tindakanRalanDokter',
                'tindakanRalanPerawat',
                'tindakanRalanDokterPerawat',
                'tindakanRanapDokter',
                'tindakanRanapPerawat',
                'tindakanRanapDokterPerawat',
            ]);
    }

    public function scopeLaporanKunjunganRalan(Builder $query)
    {
        return $query->selectRaw('COUNT(no_rawat) jumlah, DATE_FORMAT(tgl_registrasi, "%Y-%m") tgl')
            ->where('status_lanjut', 'Ralan')
            ->where('stts', '!=', 'Batal')
            ->groupByRaw('DATE_FORMAT(tgl_registrasi, "%Y-%m")');
    }

    public function pasien()
    {
        return $this->belongsTo('App\Pasien', 'no_rkm_medis', 'no_rkm_medis');
    }

    public function dokter()
    {
        return $this->belongsTo('App\Dokter', 'kd_dokter', 'kd_dokter');
    }

    public function tindakanRalanDokter()
    {
        return $this->belongsToMany('App\'TindakanRawatJalanDokterDanPerawat', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRawatJalanDokterDanPerawat::$pivotColumns)
            ->using('App\TindakanRawatJalanDokterDanPerawat');
    }

    public function tindakanRalanPerawat()
    {
        return $this->belongsToMany('App\'TindakanRawatJalanDokterDanPerawat', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRawatJalanDokterDanPerawat::$pivotColumns)
            ->using('App\TindakanRawatJalanDokterDanPerawat');
    }

    public function tindakanRalanDokterPerawat()
    {
        return $this->belongsToMany('App\'TindakanRawatJalanDokterDanPerawat', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRawatJalanDokterDanPerawat::$pivotColumns)
            ->using('App\TindakanRawatJalanDokterDanPerawat');
    }
}
