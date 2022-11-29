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
    // use HasEagerLimit;

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function scopeLaporanStatistik(
        Builder $query,
        string $periodeAwal,
        string $periodeAkhir
    ): Builder {
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

    public function scopeLaporanStatistikRekamMedis(Builder $query): Builder
    {
        return $query
            ->selectRaw(
                "reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                pasien.nm_pasien,
                pasien.no_ktp,
                pasien.jk,
                pasien.tgl_lahir,
                CONCAT(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) umur,
                pasien.agama,
                suku_bangsa.nama_suku_bangsa,
                reg_periksa.status_lanjut,
                reg_periksa.status_poli,
                reg_periksa.tgl_registrasi,
                reg_periksa.jam_reg,
                rawatinap.tgl_keluar,
                rawatinap.jam_keluar,
                rawatinap.diagnosa_awal,
                tindakan_perawatan.kode_tindakan,
                tindakan_perawatan.nama_tindakan,
                dokter.nm_dokter,
                poliklinik.nm_poli,
                rawatinap.kelas,
                penjab.png_jawab,
                rawatinap.stts_pulang,
                pasien.no_tlp,
                pasien.alamat,
                (
                    SELECT COUNT(rp2.no_rawat)
                    FROM reg_periksa rp2
                    WHERE rp2.no_rkm_medis = reg_periksa.no_rkm_medis
                    AND rp2.tgl_registrasi <= reg_periksa.tgl_registrasi
                ) kunjungan_ke"
            )
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('suku_bangsa', 'pasien.suku_bangsa', '=', 'suku_bangsa.id')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin(DB::raw("(
                SELECT
                    kamar_inap.no_rawat,
                    kamar_inap.diagnosa_awal,
                    kamar_inap.tgl_keluar,
                    kamar_inap.jam_keluar,
                    kamar.kelas,
                    kamar_inap.stts_pulang
                FROM kamar_inap
                LEFT JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                GROUP BY kamar_inap.no_rawat, kamar_inap.diagnosa_awal, kamar_inap.tgl_keluar, kamar_inap.jam_keluar, kamar.kelas, kamar_inap.stts_pulang
                HAVING max(kamar_inap.tgl_keluar)
                AND max(kamar_inap.jam_keluar)
            ) rawatinap"), 'reg_periksa.no_rawat', '=', 'rawatinap.no_rawat')
            ->leftJoin(DB::raw("(
                SELECT
                    semuarawat.no_rawat,
                    GROUP_CONCAT(' ', TRIM(jp.kd_jenis_prw), '') kode_tindakan,
                    GROUP_CONCAT(' ', TRIM(nm_perawatan), '') nama_tindakan
                FROM jns_perawatan jp
                RIGHT JOIN (
                    SELECT no_rawat, kd_jenis_prw FROM rawat_jl_dr rjd
                    UNION ALL
                    SELECT no_rawat, kd_jenis_prw FROM rawat_jl_drpr rjdp
                    UNION ALL
                    SELECT no_rawat, kd_jenis_prw FROM rawat_jl_pr rjp
                    UNION ALL
                    SELECT no_rawat, kd_jenis_prw FROM rawat_inap_dr rid
                    UNION ALL
                    SELECT no_rawat, kd_jenis_prw FROM rawat_inap_drpr ridp
                    UNION ALL
                    SELECT no_rawat, kd_jenis_prw FROM rawat_inap_pr rip
                ) semuarawat ON jp.kd_jenis_prw = semuarawat.kd_jenis_prw
                GROUP BY semuarawat.no_rawat
            ) tindakan_perawatan"), 'reg_periksa.no_rawat', '=', 'tindakan_perawatan.no_rawat')
            ->whereBetween(
                'reg_periksa.tgl_registrasi',
                ['2022-10-31', '2022-11-10']
            );
    }

    public function scopeLaporanKunjunganRalan(Builder $query): Builder
    {
        return $query->selectRaw('COUNT(no_rawat) jumlah, DATE_FORMAT(tgl_registrasi, "%Y-%m") tgl')
            ->where('status_lanjut', 'Ralan')
            ->where('stts', '!=', 'Batal')
            ->groupByRaw('DATE_FORMAT(tgl_registrasi, "%Y-%m")');
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
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_dr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanDokter::$pivotColumns)
        // ->using(TindakanRalanDokter::class);
    }

    public function tindakanRalanPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_pr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanPerawat::$pivotColumns)
        // ->using(TindakanRalanPerawat::class);
    }

    public function tindakanRalanDokterPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_drpr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRalanDokterPerawat::$pivotColumns)
        // ->using(TindakanRalanDokterPerawat::class);
    }

    public function tindakanRanapDokter(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_dr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapDokter::$pivotColumns)
        // ->using(TindakanRanapDokter::class);
    }

    public function tindakanRanapPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_pr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapPerawat::$pivotColumns)
        // ->using(TindakanRanapPerawat::class);
    }

    public function tindakanRanapDokterPerawat(): BelongsToMany
    {
        return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_drpr', 'no_rawat', 'kd_jenis_prw');
        // ->withPivot(TindakanRanapDokterPerawat::$pivotColumns)
        // ->using(TindakanRanapDokterPerawat::class);
    }
}
