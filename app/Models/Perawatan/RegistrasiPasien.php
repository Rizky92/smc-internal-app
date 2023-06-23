<?php

namespace App\Models\Perawatan;

use App\Models\Kepegawaian\Dokter;
use App\Models\RekamMedis\Pasien;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrasiPasien extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    public function umur(): Attribute
    {
        return Attribute::get(function () {
            $umur = $this->umurdaftar;
            $satuan = $this->sttsumur;

            return "({$umur} {$satuan})";
        });
    }

    public function alamatLengkap(): Attribute
    {
        return Attribute::get(function () {
            if (!($this->relationLoaded('kelurahan') ||
                $this->relationLoaded('kecamatan') ||
                $this->relationLoaded('kabupaten') ||
                $this->relationLoaded('provinsi')
            )) {
                return $this->alamat;
            }

            $alamat = $this->alamat;
            $kelurahan = optional($this->kelurahan)->nm_kel ?? '-';
            $kecamatan = optional($this->kecamatan)->nm_kec ?? '-';
            $kabupaten = optional($this->kabupaten)->nm_kab ?? '-';
            $provinsi = optional($this->provinsi)->nm_prop ?? '-';

            return "{$alamat}, Kel. {$kelurahan}, Kec. {$kecamatan}, {$kabupaten}, {$provinsi}";
        });
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function poliklinik(): BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function dokterPoli(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function penjamin(): BelongsTo
    {
        return $this->belongsTo(Penjamin::class, 'kd_pj', 'kd_pj');
    }

    public function rawatInap(): HasMany
    {
        return $this->hasMany(RawatInap::class, 'no_rawat', 'no_rawat');
    }

    public function rujukanKeluar(): HasMany
    {
        return $this->hasMany(RujukanKeluar::class, 'no_rawat', 'no_rawat');
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function diagnosa(): HasMany
    {
        return $this->hasMany(DiagnosaPasien::class, 'no_rawat', 'no_rawat')
            ->where('status', 'ralan');
    }

    public function tindakanRalanPerawat(): HasMany
    {
        return $this->hasMany(TindakanRalanPerawat::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRalanDokter(): HasMany
    {
        return $this->hasMany(TindakanRalanDokter::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRalanDokterPerawat(): HasMany
    {
        return $this->hasMany(TindakanRalanDokterPerawat::class, 'no_rawat', 'no_rawat');
    }

    public function scopeDaftarPasienRanap(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $statusPerawatan = '-',
        bool $exportToExcel = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            kamar_inap.kd_kamar,
            reg_periksa.no_rawat,
            concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal) ruangan,
            kamar.kelas,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            reg_periksa.umurdaftar,
            reg_periksa.sttsumur,
            pasien.alamat,
            kelurahan.nm_kel,
            kecamatan.nm_kec,
            kabupaten.nm_kab,
            propinsi.nm_prop,
            pasien.agama,
            concat(pasien.namakeluarga, ' (', pasien.keluarga, ')') pj,
            penjab.png_jawab,
            poliklinik.nm_poli,
            dokter.nm_dokter dokter_poli,
            kamar_inap.stts_pulang,
            kamar_inap.tgl_masuk,
            kamar_inap.jam_masuk,
            if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar) tgl_keluar,
            if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar) jam_keluar,
            kamar_inap.trf_kamar,
            kamar_inap.lama,
            kamar_inap.ttl_biaya,
            ifnull(group_concat(dokter_pj.nm_dokter separator ', '), '-') dokter_ranap,
            pasien.no_tlp
        SQL;

        if ($exportToExcel) {
            $sqlSelect = Str::after($sqlSelect, "kamar_inap.kd_kamar,");
        }

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->leftJoin('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->leftJoin('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->leftJoin('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->leftJoin('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->leftJoin('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('dpjp_ranap', 'reg_periksa.no_rawat', '=', 'dpjp_ranap.no_rawat')
            ->leftJoin(DB::raw('dokter dokter_pj'), 'dpjp_ranap.kd_dokter', '=', 'dokter_pj.kd_dokter')
            ->when($statusPerawatan === '-', fn (Builder $query) => $query->where('kamar_inap.stts_pulang', '-'))
            ->when($statusPerawatan === 'tanggal_masuk', fn (Builder $query) => $query->whereBetween('kamar_inap.tgl_masuk', [$tglAwal, $tglAkhir]))
            ->when($statusPerawatan === 'tanggal_keluar', fn (Builder $query) => $query->whereBetween('kamar_inap.tgl_keluar', [$tglAwal, $tglAkhir]))
            ->groupByRaw("
                reg_periksa.no_rawat,
                kamar_inap.kd_kamar,
                kamar_inap.tgl_masuk,
                kamar_inap.jam_masuk,
                if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar),
                if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar)
            ");
    }

    public function scopeStatusDataRM(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jenisPerawatan = 'semua',
        bool $semuaRegistrasi = false
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            reg_periksa.no_rawat,
            reg_periksa.tgl_registrasi,
            reg_periksa.stts,
            dokter.nm_dokter,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            poliklinik.nm_poli,
            reg_periksa.status_lanjut,
            exists(select * from pemeriksaan_ralan where pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat) soapie_ralan,
            exists(select * from pemeriksaan_ranap where pemeriksaan_ranap.no_rawat = reg_periksa.no_rawat) soapie_ranap,
            exists(select * from resume_pasien where resume_pasien.no_rawat = reg_periksa.no_rawat) resume_ralan,
            exists(select * from resume_pasien_ranap where resume_pasien_ranap.no_rawat = reg_periksa.no_rawat) resume_ranap,
            exists(select * from data_triase_igd where data_triase_igd.no_rawat = reg_periksa.no_rawat) triase_igd,
            exists(select * from penilaian_awal_keperawatan_igd where penilaian_awal_keperawatan_igd.no_rawat = reg_periksa.no_rawat) askep_igd,
            exists(select * from penilaian_medis_ralan where penilaian_medis_ralan.no_rawat = reg_periksa.no_rawat) askep_poli_umum,
            exists(select * from penilaian_medis_ralan_anak where penilaian_medis_ralan_anak.no_rawat = reg_periksa.no_rawat) askep_poli_anak,
            exists(select * from penilaian_medis_ralan_bedah where penilaian_medis_ralan_bedah.no_rawat = reg_periksa.no_rawat) askep_poli_bedah,
            exists(select * from penilaian_medis_ralan_bedah_mulut where penilaian_medis_ralan_bedah_mulut.no_rawat = reg_periksa.no_rawat) askep_poli_bedah_mulut,
            exists(select * from penilaian_medis_ralan_kandungan where penilaian_medis_ralan_kandungan.no_rawat = reg_periksa.no_rawat) askep_poli_kandungan,
            exists(select * from penilaian_medis_ralan_mata where penilaian_medis_ralan_mata.no_rawat = reg_periksa.no_rawat) askep_poli_mata,
            exists(select * from penilaian_medis_ralan_neurologi where penilaian_medis_ralan_neurologi.no_rawat = reg_periksa.no_rawat) askep_poli_neurologi,
            exists(select * from penilaian_medis_ralan_orthopedi where penilaian_medis_ralan_orthopedi.no_rawat = reg_periksa.no_rawat) askep_poli_orthopedi,
            exists(select * from penilaian_medis_ralan_penyakit_dalam where penilaian_medis_ralan_penyakit_dalam.no_rawat = reg_periksa.no_rawat) askep_poli_penyakit_dalam,
            exists(select * from penilaian_medis_ralan_psikiatrik where penilaian_medis_ralan_psikiatrik.no_rawat = reg_periksa.no_rawat) askep_poli_psikiatrik,
            exists(select * from penilaian_medis_ralan_tht where penilaian_medis_ralan_tht.no_rawat = reg_periksa.no_rawat) askep_poli_tht,
            exists(select * from penilaian_medis_ranap where penilaian_medis_ranap.no_rawat = reg_periksa.no_rawat) askep_ranap_umum,
            exists(select * from penilaian_medis_ranap_kandungan where penilaian_medis_ranap_kandungan.no_rawat = reg_periksa.no_rawat) askep_ranap_kandungan,
            exists(select * from diagnosa_pasien where diagnosa_pasien.no_rawat = reg_periksa.no_rawat) icd_10,
            exists(select * from prosedur_pasien where prosedur_pasien.no_rawat = reg_periksa.no_rawat) icd_9
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->when(!$semuaRegistrasi, fn (Builder $q): Builder => $q->whereNotIn('reg_periksa.stts', ['Batal', 'Belum']))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan));
    }
}
