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

    public function awalKeperawatan(): Attribute
    {
        return Attribute::get(function ($_, array $attributes) {
            $available = collect();

            if ($attributes['gigi'] !== "0") {
                $available->push('Gigi');
            }

            if ($attributes['askep_igd'] !== "0") {
                $available->push('IGD');
            }

            if ($attributes['kebidanan'] !== "0") {
                $available->push('Kebidanan');
            }

            if ($attributes['mata'] !== "0") {
                $available->push('Mata');
            }

            if ($attributes['ralan'] !== "0") {
                $available->push('Ralan');
            }

            if ($attributes['ralan_bayi'] !== "0") {
                $available->push('Ralan Bayi');
            }

            if ($attributes['ralan_psikiatri'] !== "0") {
                $available->push('Ralan Psikiatri');
            }

            if ($attributes['ranap'] !== "0") {
                $available->push('Ranap');
            }

            if ($available->isEmpty()) {
                return 'Tidak ada';
            }

            $available = $available->join(', ');

            return "Ada ($available)";
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

    public function diagnosa(): HasMany
    {
        return $this->hasMany(DiagnosaPasien::class, 'no_rawat', 'no_rawat')
            ->where('status', 'Ralan');
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

    public function scopeLaporanStatistik(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = ''
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return $query
            ->with([
                'pasien',
                'pasien.suku',
                'poliklinik',
                'dokterPoli',
                'penjamin',

                // pemeriksaan ralan pasien
                'diagnosa',
                'tindakanRalanDokter',
                // 'tindakanRalanDokter.tindakan',
                'tindakanRalanPerawat',
                // 'tindakanRalanPerawat.tindakan',
                'tindakanRalanDokterPerawat',
                // 'tindakanRalanDokterPerawat.tindakan',

                // relasi ranap pasien
                'rawatInap',
                'rawatInap.dpjpRanap',
                'rawatInap.kamar',

                // pemeriksaan ranap pasien
                'rawatInap.diagnosa',
                'rawatInap.tindakanRanapPerawat',
                // 'rawatInap.tindakanRanapPerawat.tindakan',
                'rawatInap.tindakanRanapDokter',
                // 'rawatInap.tindakanRanapDokter.tindakan',
                'rawatInap.tindakanRanapDokterPerawat',
                // 'rawatInap.tindakanRanapDokterPerawat.tindakan',
            ])
            ->whereBetween('tgl_registrasi', [$tglAwal, $tglAkhir]);
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

        $sqlSelect = "
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
        ";

        if ($exportToExcel) {
            $sqlSelect = Str::after($sqlSelect, "kamar_inap.kd_kamar,");
        }

        return $query->selectRaw($sqlSelect)
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
            ->leftJoin(DB::raw('dokter dokter_pj')->getValue(), 'dpjp_ranap.kd_dokter', '=', 'dokter_pj.kd_dokter')
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
        bool $tampilkanSemuaRegistrasi = false
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
            exists(select * from diagnosa_pasien where diagnosa_pasien.no_rawat = reg_periksa.no_rawat) icd_10,
            exists(select * from prosedur_pasien where prosedur_pasien.no_rawat = reg_periksa.no_rawat) icd_9,
            exists(select * from penilaian_awal_keperawatan_gigi where penilaian_awal_keperawatan_gigi.no_rawat = reg_periksa.no_rawat) gigi,
            exists(select * from penilaian_awal_keperawatan_kebidanan where penilaian_awal_keperawatan_kebidanan.no_rawat = reg_periksa.no_rawat) kebidanan,
            exists(select * from penilaian_awal_keperawatan_mata where penilaian_awal_keperawatan_mata.no_rawat = reg_periksa.no_rawat) mata,
            exists(select * from penilaian_awal_keperawatan_ralan where penilaian_awal_keperawatan_ralan.no_rawat = reg_periksa.no_rawat) ralan,
            exists(select * from penilaian_awal_keperawatan_ralan_bayi where penilaian_awal_keperawatan_ralan_bayi.no_rawat = reg_periksa.no_rawat) ralan_bayi,
            exists(select * from penilaian_awal_keperawatan_ralan_psikiatri where penilaian_awal_keperawatan_ralan_psikiatri.no_rawat = reg_periksa.no_rawat) ralan_psikiatri,
            exists(select * from penilaian_awal_keperawatan_ranap where penilaian_awal_keperawatan_ranap.no_rawat = reg_periksa.no_rawat) ranap
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->when(!$tampilkanSemuaRegistrasi, fn ($q) => $q->whereNotIn('reg_periksa.stts', ['Batal', 'Belum']))
            ->when($jenisPerawatan !== 'semua', fn ($q) => $q->where('reg_periksa.status_lanjut', $jenisPerawatan));
    }
}
