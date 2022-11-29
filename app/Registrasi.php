<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Registrasi extends Model
{
    // use HasEagerLimit;

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \Carbon\Carbon|\DateTime|string|null $periodeAwal
     * @param  \Carbon\Carbon|\DateTime|string|null $periodeAkhir
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLaporanStatistik($query, $periodeAwal = null, $periodeAkhir = null)
    {
        if (is_null($periodeAwal)) {
            $periodeAwal = now()->startOfMonth();
        }

        if (is_null($periodeAkhir)) {
            $periodeAkhir = now()->endOfMonth();
        }

        if (is_string($periodeAwal) && Carbon::hasFormat($periodeAwal, 'd-m-Y')) {
            $periodeAwal = Carbon::createFromFormat('d-m-Y', $periodeAwal);
        }

        if (is_string($periodeAkhir) && Carbon::hasFormat($periodeAkhir, 'd-m-Y')) {
            $periodeAkhir = Carbon::createFromFormat('d-m-Y', $periodeAkhir);
        }

        if ($periodeAwal instanceof Carbon || $periodeAwal instanceof DateTime) {
            $periodeAwal = $periodeAwal->format('Y-m-d');
        }

        if ($periodeAkhir instanceof Carbon || $periodeAkhir instanceof DateTime) {
            $periodeAkhir = $periodeAkhir->format('Y-m-d');
        }

        return $query
            ->with([
                'pasien:no_rkm_medis,nm_pasien,no_ktp,jk,tgl_lahir,agama,suku_bangsa,no_tlp,alamat',
                'pasien.suku',
                'dokter:kd_dokter,nm_dokter',
                'poliklinik:kd_poli,nm_poli',
                'penjamin:kd_pj,png_jawab',
                'diagnosa',
                'rawatInap',
                'tindakanRalanDokter:kd_jenis_prw,nm_perawatan',
                'tindakanRalanPerawat:kd_jenis_prw,nm_perawatan',
                'tindakanRalanDokterPerawat:kd_jenis_prw,nm_perawatan',
                'tindakanRanapDokter:kd_jenis_prw,nm_perawatan',
                'tindakanRanapPerawat:kd_jenis_prw,nm_perawatan',
                'tindakanRanapDokterPerawat:kd_jenis_prw,nm_perawatan',
            ])
            ->Select(DB::raw("(
                SELECT COUNT(rp2.no_rkm_medis)
                FROM reg_periksa rp2
                WHERE rp2.no_rkm_medis = reg_periksa.no_rkm_medis
                AND rp2.tgl_registrasi <= reg_periksa.tgl_registrasi
            ) kunjungan_ke, reg_periksa.*"))
            ->whereBetween(
                'tgl_registrasi',
                [$periodeAwal, $periodeAkhir]
            );
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLaporanKunjunganRalan($query)
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
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penjamin()
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rawatInap()
    {
        return $this->belongsToMany(Kamar::class, 'kamar_inap', 'no_rawat', 'kd_kamar')
            ->withPivot(RawatInap::$pivotColumns)
            ->using(RawatInap::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diagnosa()
    {
        return $this->belongsToMany(Penyakit::class, 'diagnosa_pasien', 'no_rawat', 'kd_penyakit');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanDokter()
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_dr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanDokter::$pivotColumns)
        // ->using(TindakanRalanDokter::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanPerawat()
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_pr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanPerawat::$pivotColumns)
        // ->using(TindakanRalanPerawat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRalanDokterPerawat()
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_drpr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanDokterPerawat::$pivotColumns)
        // ->using(TindakanRalanDokterPerawat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapDokter()
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_dr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapDokter::$pivotColumns)
        // ->using(TindakanRanapDokter::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapPerawat()
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_pr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapPerawat::$pivotColumns)
        // ->using(TindakanRanapPerawat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tindakanRanapDokterPerawat()
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_drpr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapDokterPerawat::$pivotColumns)
        // ->using(TindakanRanapDokterPerawat::class);
    }
}
