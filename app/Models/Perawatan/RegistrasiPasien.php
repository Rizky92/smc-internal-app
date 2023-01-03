<?php

namespace App\Models\Perawatan;

use App\Models\Dokter;
use App\Models\RekamMedis\Pasien;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class RegistrasiPasien extends Model
{
    public const JAM_AWAL = '18:00:00';
    public const JAM_AKHIR = '06:00:00';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public $fillable = [
        'status_lanjut',
        'stts',
    ];

    public function scopeDaftarPasienRanap(
        Builder $query,
        string $cari = '',
        string $statusPerawatan = '-',
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jamAwal = '',
        string $jamAkhir = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        if (empty($jamAwal)) {
            $jamAwal = '18:00:00';
        }

        if (empty($jamAkhir)) {
            $jamAkhir = '06:00:00';
        }

        return $query->selectRaw("
            reg_periksa.no_rawat,
            reg_periksa.no_rkm_medis,
            concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')') data_pasien,
            concat(pasien.alamat, ', Kel. ', kelurahan.nm_kel, ', Kec. ', kecamatan.nm_kec, ', ', kabupaten.nm_kab, ', ', propinsi.nm_prop) alamat_pasien,
            pasien.agama,
            concat(pasien.namakeluarga, ' (', pasien.keluarga, ')') pj,
            penjab.png_jawab,
            concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) ruangan,
            kamar_inap.kd_kamar,
            kamar_inap.trf_kamar,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar) tgl_keluar,
            if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar) jam_keluar,
            kamar_inap.lama,
            kamar_inap.stts_pulang,
            (
                SELECT dokter.nm_dokter FROM dokter JOIN dpjp_ranap ON dpjp_ranap.kd_dokter = dokter.kd_dokter WHERE dpjp_ranap.no_rawat = reg_periksa.no_rawat LIMIT 1
            ) nama_dokter,
            pasien.no_tlp,
            poliklinik.nm_poli,
            dokter.nm_dokter dokter_poli
        ")
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->join('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->join('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->join('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->when($statusPerawatan == '-', function (Builder $query) {
                return $query->where('kamar_inap.stts_pulang', '-');
            })
            ->when($statusPerawatan != '-', function (Builder $query) use ($statusPerawatan, $tglAwal, $tglAkhir, $jamAwal, $jamAkhir) {
                switch (Str::snake($statusPerawatan)) {
                    case 'tanggal_masuk':
                        return $query->where(function (Builder $query) use ($tglAwal, $tglAkhir, $jamAwal, $jamAkhir) {
                            return $query->where('kamar_inap.stts_pulang', '-')
                                ->whereBetween('kamar_inap.tgl_masuk', [$tglAwal, $tglAkhir])
                                ->where(function (Builder $query) use ($jamAwal, $jamAkhir) {
                                    return $query->where('kamar_inap.jam_masuk', '<', $jamAkhir)
                                        ->orWhere('kamar_inap.jam_masuk', '>', $jamAwal);
                                });
                        });
                    case 'tanggal_keluar':
                        return $query->where(function (Builder $query) use ($tglAwal, $tglAkhir, $jamAwal, $jamAkhir) {
                            return $query->whereNotIn('kamar_inap.stts_pulang', ['-', 'pindah kamar'])
                                ->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir])
                                ->where(function (Builder $query) use ($jamAwal, $jamAkhir) {
                                    return $query->where('kamar_inap.jam_keluar', '<', $jamAkhir)
                                        ->orWhere('kamar_inap.jam_keluar', '>', $jamAwal);
                                });
                        });
                }
            })
            ->when(!empty($cari), function (Builder $query) use ($cari) {
                return $query->where(function (Builder $query) use ($cari) {
                    return $query
                        ->where('pasien.nm_pasien', 'LIKE', "%{$cari}%")
                        ->orWhere('bangsal.nm_bangsal', 'LIKE', "%{$cari}%")
                        ->orWhere('bangsal.kd_bangsal', 'LIKE', "%{$cari}%")
                        ->orWhere('kamar.kd_kamar', 'LIKE', "%{$cari}%")
                        ->orWhere('reg_periksa.no_rawat', 'LIKE', "%{$cari}%")
                        ->orWhere('pasien.no_rkm_medis', 'LIKE', "%{$cari}%");
                });
            });
    }

    public function scopeKunjungan(Builder $query, string $poli = ''): Builder
    {
        return $query->selectRaw("
            COUNT(reg_periksa.no_rawat) jumlah,
            DATE_FORMAT(reg_periksa.tgl_registrasi, '%m-%Y') bulan
        ")
            ->whereNotIn('reg_periksa.stts', ['Belum', 'Batal'])
            ->where('reg_periksa.status_bayar', 'Sudah Bayar')
            ->when(!empty($poli), function (Builder $query) use ($poli) {
                switch (Str::lower($poli)) {
                    case 'ralan':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'ranap':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ranap')
                            ->whereNotIn('reg_periksa.kd_poli', ['U0056', 'U0057', 'IGDK']);
                    case 'igd':
                        return $query->where('reg_periksa.kd_poli', '=', 'IGDK');
                    case 'walkin':
                        return $query->where('reg_periksa.status_lanjut', '=', 'Ralan')
                            ->whereIn('reg_periksa.kd_poli', ['U0056', 'U0057']);
                }
            })
            ->whereBetween('reg_periksa.tgl_registrasi', [
                now()->startOfYear()->format('Y-m-d'),
                now()->endOfYear()->format('Y-m-d')
            ])
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

    public function dpjp(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'dpjp_ranap', 'no_rawat', 'kd_dokter');
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
        return $this->belongsToMany(Kamar::class, 'kamar_inap', 'no_rawat', 'kd_kamar');
    }

    public function diagnosa(): BelongsToMany
    {
        return $this->belongsToMany(Penyakit::class, 'diagnosa_pasien', 'no_rawat', 'kd_penyakit');
    }

    // public function tindakanRalanDokter(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_dr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRalanDokter::$pivotColumns)
    //         ->using(TindakanRalanDokter::class);
    // }

    // public function tindakanRalanPerawat(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_pr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRalanPerawat::$pivotColumns)
    //         ->using(TindakanRalanPerawat::class);
    // }

    // public function tindakanRalanDokterPerawat(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRalan::class, 'rawat_jl_drpr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRalanDokterPerawat::$pivotColumns)
    //         ->using(TindakanRalanDokterPerawat::class);
    // }

    // public function tindakanRanapDokter(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_dr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRanapDokter::$pivotColumns)
    //         ->using(TindakanRanapDokter::class);
    // }

    // public function tindakanRanapPerawat(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_pr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRanapPerawat::$pivotColumns)
    //         ->using(TindakanRanapPerawat::class);
    // }

    // public function tindakanRanapDokterPerawat(): BelongsToMany
    // {
    //     return $this->belongsToMany(JenisPerawatanRanap::class, 'rawat_inap_drpr', 'no_rawat', 'kd_jenis_prw')
    //         ->withPivot(TindakanRanapDokterPerawat::$pivotColumns)
    //         ->using(TindakanRanapDokterPerawat::class);
    // }

    public static function totalKunjunganRalan(): array
    {
        return (new static)->kunjungan('Ralan')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganRanap(): array
    {
        return (new static)->kunjungan('Ranap')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }

    public static function totalKunjunganIGD(): array
    {
        return (new static)->kunjungan('Igd')
            ->get()
            ->map(function ($value, $key) {
                return [$value->bulan => $value->jumlah];
            })->flatten(1)->pad(-12, 0)->toArray();
    }
}
