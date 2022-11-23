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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pasien()
    {
        return $this->belongsTo('App\Pasien', 'no_rkm_medis', 'no_rkm_medis');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dokter()
    {
        return $this->belongsTo('App\Dokter', 'kd_dokter', 'kd_dokter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diagnosa()
    {
        return $this->belongsToMany('App\Penyakit', 'diagnosa_penyakit', 'no_rawat', 'kd_penyakit');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanDokter()
    {
        return $this->belongsToMany('App\JenisPerawatanRalan', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanDokter::$pivotColumns)
            ->using('App\TindakanRalanDokter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanPerawat()
    {
        return $this->belongsToMany('App\JenisPerawatanRalan', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanPerawat::$pivotColumns)
            ->using('App\TindakanRalanPerawat');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanDokterPerawat()
    {
        return $this->belongsToMany('App\JenisPerawatanRalan', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRalanDokterPerawat::$pivotColumns)
            ->using('App\TindakanRalanDokterPerawat');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapDokter()
    {
        return $this->belongsToMany('App\JenisPerawatanRanap', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapDokter::$pivotColumns)
            ->using('App\TindakanRanapDokter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapPerawat()
    {
        return $this->belongsToMany('App\JenisPerawatanRanap', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapPerawat::$pivotColumns)
            ->using('App\TindakanRanapPerawat');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapDokterPerawat()
    {
        return $this->belongsToMany('App\JenisPerawatanRanap', 'jns_perawatan', 'no_rawat', 'kd_jenis_prw')
            ->withPivot(TindakanRanapDokterPerawat::$pivotColumns)
            ->using('App\TindakanRanapDokterPerawat');
    }
}
