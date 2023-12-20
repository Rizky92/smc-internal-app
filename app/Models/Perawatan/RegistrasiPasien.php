<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Models\Farmasi\PemberianObat;
use App\Models\Perawatan\Poliklinik;
use App\Models\Kepegawaian\Dokter;
use App\Models\Laboratorium\HasilPeriksaLab;
use App\Models\Laboratorium\PermintaanLabMB;
use App\Models\Laboratorium\PermintaanLabPA;
use App\Models\Laboratorium\PermintaanLabPK;
use App\Models\Radiologi\HasilPeriksaRadiologi;
use App\Models\Radiologi\PermintaanRadiologi;
use App\Models\RekamMedis\BerkasDigitalKeperawatan;
use App\Models\RekamMedis\Pasien;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class RegistrasiPasien extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'reg_periksa';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_reg',
        'no_rawat',
        'kd_dokter',
        'no_rkm_medis',
        'kd_poli',
        'p_jawab',
        'almt_pj',
        'hubunganpj',
        'stts',
        'stts_daftar',
        'status_lanjut',
        'kd_pj',
        'status_bayar',
        'status_poli',
    ];

    public function umur(): Attribute
    {
        return Attribute::get(fn (): string => "({$this->umurdaftar} {$this->sttsumur})");
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

    public function asmedRWI(): Attribute
    {
        return Attribute::get(function ($_, array $attributes) {
            $asmed = collect();

            if ($attributes['asmed_ranap_umum'] === "1") {
                $asmed->push('RWI Umum');
            }

            if ($attributes['asmed_ranap_kandungan'] === "1") {
                $asmed->push('RWI Kandungan');
            }

            if ($asmed->isEmpty()) {
                return 'Tidak ada';
            }

            return $asmed->joinStr(', ')->wrap('Ada (', ')')->value();
        });
    }

    public function asmedPoli(): Attribute
    {
        return Attribute::get(function ($_, array $attributes) {
            $asmed = collect();

            if ($attributes['asmed_poli_umum'] === "1") {
                $asmed->push('Poli Umum');
            }

            if ($attributes['asmed_poli_anak'] === "1") {
                $asmed->push('Poli Anak');
            }

            if ($attributes['asmed_poli_bedah'] === "1") {
                $asmed->push('Poli Bedah');
            }

            if ($attributes['asmed_poli_bedah_mulut'] === "1") {
                $asmed->push('Poli Bedah Mulut');
            }

            if ($attributes['asmed_poli_kandungan'] === "1") {
                $asmed->push('Poli Kandungan');
            }

            if ($attributes['asmed_poli_mata'] === "1") {
                $asmed->push('Poli Mata');
            }

            if ($attributes['asmed_poli_neurologi'] === "1") {
                $asmed->push('Poli Neurologi');
            }

            if ($attributes['asmed_poli_orthopedi'] === "1") {
                $asmed->push('Poli Orthopedi');
            }

            if ($attributes['asmed_poli_penyakit_dalam'] === "1") {
                $asmed->push('Poli Penyakit Dalam');
            }

            if ($attributes['asmed_poli_psikiatrik'] === "1") {
                $asmed->push('Poli Psikiatrik');
            }

            if ($attributes['asmed_poli_tht'] === "1") {
                $asmed->push('Poli THT');
            }

            if ($attributes['asmed_poli_geriatri'] === "1") {
                $asmed->push('Poli Geriatri');
            }

            if ($attributes['asmed_poli_kulit_kelamin'] === "1") {
                $asmed->push('Poli Kulit & Kelamin');
            }

            if ($asmed->isEmpty()) {
                return "Tidak ada";
            }

            return $asmed->joinStr(', ')->wrap('Ada (', ')')->value();
        });
    }

    public function askepRalan(): Attribute
    {
        return Attribute::get(function ($_, array $attributes): ?string {
            $askep = collect();

            if ($attributes['askep_ralan_umum'] === "1") {
                $askep->push("Umum");
            }

            if ($attributes['askep_ralan_bidan'] === "1") {
                $askep->push("Kebidanan");
            }

            if ($attributes['askep_ralan_gigi'] === "1") {
                $askep->push("Gigi");
            }

            if ($attributes['askep_ralan_bayi'] === "1") {
                $askep->push("Bayi");
            }

            if ($attributes['askep_ralan_psikiatri'] === "1") {
                $askep->push("Psikiatri");
            }

            if ($attributes['askep_ralan_geriatri'] === "1") {
                $askep->push("Geriatri");
            }

            if ($askep->isEmpty()) {
                return "Tidak ada";
            }

            return 'Ada ' . $askep->joinStr(', ')->wrap('(', ')')->value();
        });
    }

    public function askepRanap(): Attribute
    {
        return Attribute::get(function ($_, array $attributes): ?string {
            $askep = collect();

            if ($attributes['askep_ranap_umum'] === "1") {
                $askep->push("Umum");
            }

            if ($attributes['askep_ranap_bidan'] === "1") {
                $askep->push("Kebidanan");
            }

            if ($askep->isEmpty()) {
                return "Tidak ada";
            }

            return $askep->joinStr(', ')->wrap('Ada (', ')')->value();
        });
    }

    public function statusOrderLab(): Attribute
    {
        return Attribute::get(function ($_, array $attributes): ?string {
            if (
                !$this->relationLoaded('permintaanLabPK') &&
                !$this->relationLoaded('permintaanLabPA')
            ) {
                return null;
            }

            if (
                $this->permintaanLabPK->isEmpty() &&
                $this->permintaanLabPA->isEmpty()
            ) {
                return 'Tidak ada';
            }

            $statusOrderPK = optional($this->permintaanLabPK)->containsStrict('status_order', 'Sudah Dilayani');
            $statusOrderPA = optional($this->permintaanLabPA)->containsStrict('status_order', 'Sudah Dilayani');

            return ($statusOrderPK || $statusOrderPA)
                ? 'Sudah Dilayani'
                : 'Belum Dilayani';
        });
    }

    public function statusOrderRad(): Attribute
    {
        return Attribute::get(function ($_, array $attributes): ?string {
            if (!$this->relationLoaded('permintaanRadiologi')) {
                return null;
            }

            if ($this->permintaanRadiologi->isEmpty()) {
                return 'Tidak ada';
            }

            return optional($this->permintaanRadiologi)->containsStrict('status_order', 'Sudah Dilayani')
                ? 'Sudah Dilayani'
                : 'Belum Dilayani';
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

    public function diagnosa(): HasMany
    {
        return $this->hasMany(DiagnosaPasien::class, 'no_rawat', 'no_rawat');
    }

    public function permintaanLabPK(): HasMany
    {
        return $this->hasMany(PermintaanLabPK::class, 'no_rawat', 'no_rawat');
    }

    public function permintaanLabPA(): HasMany
    {
        return $this->hasMany(PermintaanLabPA::class, 'no_rawat', 'no_rawat');
    }

    public function permintaanLabMB(): HasMany
    {
        return $this->hasMany(PermintaanLabMB::class, 'no_rawat', 'no_rawat');
    }

    public function permintaanRadiologi(): HasMany
    {
        return $this->hasMany(PermintaanRadiologi::class, 'no_rawat', 'no_rawat');
    }

    public function hasilLaboratorium(): HasMany
    {
        return $this->hasMany(HasilPeriksaLab::class, 'no_rawat', 'no_rawat');
    }

    public function hasilRadiologi(): HasMany
    {
        return $this->hasMany(HasilPeriksaRadiologi::class, 'no_rawat', 'no_rawat');
    }

    public function pemberianObat(): HasMany
    {
        return $this->hasMany(PemberianObat::class, 'no_rawat', 'no_rawat');
    }

    public function berkasDigital(): HasMany
    {
        return $this->hasMany(BerkasDigitalKeperawatan::class, 'no_rawat', 'no_rawat');
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

    public function tindakanRanapPerawat(): HasMany
    {
        return $this->hasMany(TindakanRanapPerawat::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRanapDokter(): HasMany
    {
        return $this->hasMany(TindakanRanapDokter::class, 'no_rawat', 'no_rawat');
    }

    public function tindakanRanapDokterPerawat(): HasMany
    {
        return $this->hasMany(TindakanRanapDokterPerawat::class, 'no_rawat', 'no_rawat');
    }

    public function scopeLaporanStatistik(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfWeek()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfWeek()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            reg_periksa.no_rawat as no_rawat,
            reg_periksa.no_rkm_medis as no_rm,
            pasien.nm_pasien as nm_pasien,
            pasien.no_ktp as no_ktp,
            pasien.jk as jk,
            pasien.tgl_lahir as tgl_lahir,
            reg_periksa.umurdaftar,
            reg_periksa.sttsumur,
            pasien.agama as agama,
            suku_bangsa.nama_suku_bangsa as suku,
            reg_periksa.status_lanjut as status_lanjut,
            concat_ws(' ', rawat_inap.kd_kamar, bangsal.nm_bangsal) as ruangan,
            reg_periksa.status_poli as status_poli,
            poliklinik.nm_poli as nm_poli,
            dokter.nm_dokter as nm_dokter,
            reg_periksa.stts as status,
            reg_periksa.tgl_registrasi as tgl_registrasi,
            reg_periksa.jam_reg as jam_registrasi,
            if(rawat_inap.tgl_keluar > '0000-00-00', rawat_inap.tgl_keluar, '-') as tgl_keluar,
            if(rawat_inap.jam_keluar > '00:00:00', rawat_inap.jam_keluar, '-') as jam_keluar,
            rawat_inap.diagnosa_awal as diagnosa_awal,
            group_concat(distinct diagnosa_pasien.kd_penyakit order by diagnosa_pasien.prioritas asc separator '; ') as icd_diagnosa,
            group_concat(distinct trim(concat_ws(' - ', diagnosa_pasien.kd_penyakit, penyakit.nm_penyakit)) order by diagnosa_pasien.prioritas asc separator '; ') as diagnosa,
            group_concat(distinct perawatan_ralan.kd_jenis_prw separator '; ') as kd_tindakan_ralan,
            group_concat(distinct trim(concat_ws(' - ', perawatan_ralan.kd_jenis_prw, jns_perawatan.nm_perawatan)) separator '; ') as nm_tindakan_ralan,
            group_concat(distinct perawatan_ranap.kd_jenis_prw separator '; ') as kd_tindakan_ranap,
            group_concat(distinct trim(concat_ws(' - ', perawatan_ranap.kd_jenis_prw, jns_perawatan_inap.nm_perawatan)) separator '; ') as nm_tindakan_ranap,
            '-' as lama_operasi,
            rujuk_masuk.perujuk as rujukan_masuk,
            group_concat(distinct dokter_dpjp.nm_dokter separator '; ') as dokter_pj,
            kamar.kelas as kelas,
            penjab.png_jawab as penjamin,
            reg_periksa.status_bayar as status_bayar,
            rawat_inap.stts_pulang as status_pulang_ranap,
            rujuk.rujuk_ke as rujuk_keluar_rs,
            convert(pasien.alamat using ascii) as alamat,
            pasien.no_tlp as no_hp,
            (select count(rp2.no_rawat) from reg_periksa rp2 where rp2.no_rkm_medis = reg_periksa.no_rkm_medis and rp2.tgl_registrasi <= reg_periksa.tgl_registrasi) as kunjungan_ke
        SQL;

        $this->addSearchConditions([
            'pasien.nm_pasien',
            'pasien.no_ktp',
            'suku_bangsa.nama_suku_bangsa',
            'poliklinik.nm_poli',
            'dokter.nm_dokter',
            'rawat_inap.diagnosa_awal',
            'diagnosa_pasien.kd_penyakit',
            'penyakit.nm_penyakit',
            'perawatan_ralan.kd_jenis_prw',
            'jns_perawatan.nm_perawatan',
            'perawatan_ranap.kd_jenis_prw',
            'kamar.kd_kamar',
            'bangsal.kd_bangsal',
            'bangsal.nm_bangsal',
            'jns_perawatan_inap.nm_perawatan',
            'rujuk_masuk.perujuk',
            'dokter_dpjp.kd_dokter',
            'dokter_dpjp.nm_dokter',
            'kamar.kelas',
            'penjab.png_jawab',
            'rujuk.rujuk_ke',
            'pasien.alamat',
            'pasien.no_tlp'
        ]);

        $rawatInap = RawatInap::query()
            ->select(['kd_kamar', 'no_rawat', 'diagnosa_awal', 'tgl_keluar', 'jam_keluar', 'lama', 'stts_pulang'])
            ->whereNotIn('kamar_inap.stts_pulang', ['Pindah Kamar'])
            ->orderByRaw(<<<SQL
                cast(
                    concat_ws(' ',
                        if (
                            kamar_inap.tgl_keluar is not null or kamar_inap.tgl_keluar > '0000-00-00',
                            kamar_inap.tgl_keluar,
                            current_date()
                        ),
                        if (
                            kamar_inap.jam_keluar is not null or kamar_inap.jam_keluar > '00:00:00',
                            kamar_inap.jam_keluar,
                            current_time()
                        )
                    ) as datetime
                ) desc
            SQL);

        $perawatanRalan = TindakanRalanDokterPerawat::query()
            ->select(['no_rawat', 'kd_jenis_prw'])
            ->unionAll(TindakanRalanDokter::select(['no_rawat', 'kd_jenis_prw']))
            ->unionAll(TindakanRalanPerawat::select(['no_rawat', 'kd_jenis_prw']));

        $perawatanRanap = TindakanRanapDokterPerawat::query()
            ->select(['no_rawat', 'kd_jenis_prw'])
            ->unionAll(TindakanRanapDokter::select(['no_rawat', 'kd_jenis_prw']))
            ->unionAll(TindakanRanapPerawat::select(['no_rawat', 'kd_jenis_prw']));

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['kunjungan_ke' => 'int'])
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('suku_bangsa', 'pasien.suku_bangsa', 'suku_bangsa.id')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoinSub($rawatInap, 'rawat_inap', fn (JoinClause $join) =>
                $join->on('reg_periksa.no_rawat', '=', 'rawat_inap.no_rawat'))
            ->leftJoin('dpjp_ranap', 'reg_periksa.no_rawat', '=', 'dpjp_ranap.no_rawat')
            ->leftJoin(DB::raw('dokter dokter_dpjp'), 'dpjp_ranap.kd_dokter', '=', 'dokter_dpjp.kd_dokter')
            ->leftJoin('kamar', 'rawat_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->leftJoin('rujuk', 'reg_periksa.no_rawat', '=', 'rujuk.no_rawat')
            ->leftJoin('rujuk_masuk', 'reg_periksa.no_rawat', '=', 'rujuk_masuk.no_rawat')
            ->leftJoin('diagnosa_pasien', 'reg_periksa.no_rawat', '=', 'diagnosa_pasien.no_rawat')
            ->leftJoin('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->leftJoinSub($perawatanRalan, 'perawatan_ralan', fn (JoinClause $join) =>
                $join->on('reg_periksa.no_rawat', '=', 'perawatan_ralan.no_rawat'))
            ->leftJoin('jns_perawatan', 'perawatan_ralan.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->leftJoinSub($perawatanRanap, 'perawatan_ranap', fn (JoinClause $join) =>
                $join->on('reg_periksa.no_rawat', '=', 'perawatan_ranap.no_rawat'))
            ->leftJoin('jns_perawatan_inap', 'perawatan_ranap.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->groupByRaw('reg_periksa.no_rawat');
    }

    public function scopeDaftarPasienRanap(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $statusPerawatan = '-'
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
            bangsal.nm_bangsal,
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

        $this->addSearchConditions([
            'kamar_inap.kd_kamar',
            'kamar.kd_kamar',
            'bangsal.kd_bangsal',
            'bangsal.nm_bangsal',
            'kamar.kelas',
            'pasien.nm_pasien',
            'pasien.alamat',
            'kelurahan.nm_kel',
            'kecamatan.nm_kec',
            'kabupaten.nm_kab',
            'propinsi.nm_prop',
            'pasien.agama',
            'pasien.namakeluarga',
            'pasien.keluarga',
            'penjab.png_jawab',
            'poliklinik.nm_poli',
            'dokter.nm_dokter',
            'kamar_inap.stts_pulang',
            'ifnull(dokter_pj.nm_dokter, "-")',
            'pasien.no_tlp',
        ]);

        $this->addSortColumns([
            'ruangan'       => DB::raw("concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal)"),
            'data_pasien'   => DB::raw("concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')')"),
            'alamat_pasien' => DB::raw("concat(pasien.alamat, ', Kel. ', kelurahan.nm_kel, ', Kec. ', kecamatan.nm_kec, ', ', kabupaten.nm_kab, ', ', propinsi.nm_prop)"),
            'pj'            => DB::raw("concat(pasien.namakeluarga, ' (', pasien.keluarga, ')')"),
            'dokter_poli'   => "dokter.nm_dokter",
            'tgl_keluar'    => DB::raw("if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar)"),
            'jam_keluar'    => DB::raw("if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar)"),
            'dokter_ranap'  => DB::raw("group_concat(dokter_pj.nm_dokter separator ', ')"),
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['trf_kamar' => 'float', 'lama' => 'int', 'ttl_biaya' => 'float'])
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
            ->groupByRaw(<<<SQL
                reg_periksa.no_rawat,
                kamar_inap.kd_kamar,
                kamar_inap.tgl_masuk,
                kamar_inap.jam_masuk,
                if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar),
                if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar)
            SQL);
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
            exists(select * from penilaian_awal_keperawatan_ralan where penilaian_awal_keperawatan_ralan.no_rawat = reg_periksa.no_rawat) askep_ralan_umum,
            exists(select * from penilaian_awal_keperawatan_gigi where penilaian_awal_keperawatan_gigi.no_rawat = reg_periksa.no_rawat) askep_ralan_gigi,
            exists(select * from penilaian_awal_keperawatan_kebidanan where penilaian_awal_keperawatan_kebidanan.no_rawat = reg_periksa.no_rawat) askep_ralan_bidan,
            exists(select * from penilaian_awal_keperawatan_ralan_bayi where penilaian_awal_keperawatan_ralan_bayi.no_rawat = reg_periksa.no_rawat) askep_ralan_bayi,
            exists(select * from penilaian_awal_keperawatan_ralan_psikiatri where penilaian_awal_keperawatan_ralan_psikiatri.no_rawat = reg_periksa.no_rawat) askep_ralan_psikiatri,
            exists(select * from penilaian_awal_keperawatan_ralan_geriatri where penilaian_awal_keperawatan_ralan_geriatri.no_rawat = reg_periksa.no_rawat) askep_ralan_geriatri,
            exists(select * from penilaian_awal_keperawatan_igd where penilaian_awal_keperawatan_igd.no_rawat = reg_periksa.no_rawat) askep_igd,
            exists(select * from penilaian_awal_keperawatan_ranap where penilaian_awal_keperawatan_ranap.no_rawat = reg_periksa.no_rawat) askep_ranap_umum,
            exists(select * from penilaian_awal_keperawatan_kebidanan_ranap where penilaian_awal_keperawatan_kebidanan_ranap.no_rawat = reg_periksa.no_rawat) askep_ranap_bidan,
            exists(select * from penilaian_medis_igd where penilaian_medis_igd.no_rawat = reg_periksa.no_rawat) asmed_igd,
            exists(select * from penilaian_medis_ralan where penilaian_medis_ralan.no_rawat = reg_periksa.no_rawat) asmed_poli_umum,
            exists(select * from penilaian_medis_ralan_anak where penilaian_medis_ralan_anak.no_rawat = reg_periksa.no_rawat) asmed_poli_anak,
            exists(select * from penilaian_medis_ralan_bedah where penilaian_medis_ralan_bedah.no_rawat = reg_periksa.no_rawat) asmed_poli_bedah,
            exists(select * from penilaian_medis_ralan_bedah_mulut where penilaian_medis_ralan_bedah_mulut.no_rawat = reg_periksa.no_rawat) asmed_poli_bedah_mulut,
            exists(select * from penilaian_medis_ralan_kandungan where penilaian_medis_ralan_kandungan.no_rawat = reg_periksa.no_rawat) asmed_poli_kandungan,
            exists(select * from penilaian_medis_ralan_mata where penilaian_medis_ralan_mata.no_rawat = reg_periksa.no_rawat) asmed_poli_mata,
            exists(select * from penilaian_medis_ralan_neurologi where penilaian_medis_ralan_neurologi.no_rawat = reg_periksa.no_rawat) asmed_poli_neurologi,
            exists(select * from penilaian_medis_ralan_orthopedi where penilaian_medis_ralan_orthopedi.no_rawat = reg_periksa.no_rawat) asmed_poli_orthopedi,
            exists(select * from penilaian_medis_ralan_penyakit_dalam where penilaian_medis_ralan_penyakit_dalam.no_rawat = reg_periksa.no_rawat) asmed_poli_penyakit_dalam,
            exists(select * from penilaian_medis_ralan_psikiatrik where penilaian_medis_ralan_psikiatrik.no_rawat = reg_periksa.no_rawat) asmed_poli_psikiatrik,
            exists(select * from penilaian_medis_ralan_tht where penilaian_medis_ralan_tht.no_rawat = reg_periksa.no_rawat) asmed_poli_tht,
            exists(select * from penilaian_medis_ralan_geriatri where penilaian_medis_ralan_geriatri.no_rawat = reg_periksa.no_rawat) asmed_poli_geriatri,
            exists(select * from penilaian_medis_ralan_kulitdankelamin where penilaian_medis_ralan_kulitdankelamin.no_rawat = reg_periksa.no_rawat) asmed_poli_kulit_kelamin,
            exists(select * from penilaian_medis_ranap where penilaian_medis_ranap.no_rawat = reg_periksa.no_rawat) asmed_ranap_umum,
            exists(select * from penilaian_medis_ranap_kandungan where penilaian_medis_ranap_kandungan.no_rawat = reg_periksa.no_rawat) asmed_ranap_kandungan,
            exists(select * from diagnosa_pasien where diagnosa_pasien.no_rawat = reg_periksa.no_rawat) icd_10,
            exists(select * from prosedur_pasien where prosedur_pasien.no_rawat = reg_periksa.no_rawat) icd_9
        SQL;

        $this->addSearchConditions([
            'dokter.nm_dokter',
            'pasien.nm_pasien',
            'poliklinik.nm_poli',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->when(! $semuaRegistrasi, fn (Builder $q): Builder => $q->whereNotIn('reg_periksa.stts', ['Batal', 'Belum']))
            ->when($jenisPerawatan !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenisPerawatan));
    }

    public function scopeLaporanTransaksiGantung(
        Builder $query,
        string $tglAwal = '',
        string $tglAkhir = '',
        string $jenis = 'ralan',
        string $status = 'sudah'
    ): Builder {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            dokter.nm_dokter,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            poliklinik.nm_poli,
            reg_periksa.p_jawab,
            reg_periksa.almt_pj,
            reg_periksa.hubunganpj,
            coalesce(penjab.nama_perusahaan, penjab.png_jawab) penjamin,
            reg_periksa.stts,
            reg_periksa.no_rawat,
            reg_periksa.tgl_registrasi,
            reg_periksa.jam_reg
        SQL;

        $this->addSearchConditions([
            'reg_periksa.no_rawat',
            'reg_periksa.no_rkm_medis',
            'reg_periksa.kd_poli',
            'reg_periksa.p_jawab',
            'reg_periksa.almt_pj',
            'reg_periksa.kd_pj',
            'pasien.nm_pasien',
            'coalesce(penjab.nama_perusahaan, penjab.png_jawab, "-")',
            'dokter.nm_dokter',
            'poliklinik.nm_poli',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->with(['permintaanLabPK', 'permintaanLabPA', 'permintaanRadiologi'])
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->when($jenis !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.status_lanjut', $jenis))
            ->when($status !== 'semua', fn (Builder $q): Builder => $q->where('reg_periksa.stts', $status))
            ->where('reg_periksa.status_bayar', 'belum bayar')
            ->withExists([
                'diagnosa as diagnosa' => fn (Builder $q): Builder => $q->where('status', $jenis),
                'pemberianObat as obat',
                'tindakanRalanPerawat as ralan_perawat',
            ]);
    }

    public function scopeRiwayatPemakaianObatTB(Builder $query, string $tglAwal = '', string $tglAkhir = '', string $cari = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            reg_periksa.no_rawat,
            reg_periksa.tgl_registrasi,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            pasien.no_ktp,
            databarang.nama_brng,
            sum(detail_pemberian_obat.jml) as total,
            bangsal.nm_bangsal,
            reg_periksa.status_lanjut,
            penjab.png_jawab,
            pasien.no_tlp,
            pasien.alamat
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['total' => 'float'])
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('detail_pemberian_obat', 'reg_periksa.no_rawat', '=', 'detail_pemberian_obat.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('bangsal', 'detail_pemberian_obat.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tglAwal, $tglAkhir])
            ->whereIn('databarang.kode_kategori', ['2.14', '2.15'])
            ->search($cari, [
                'pasien.nm_pasien',
                'databarang.nama_brng',
                'bangsal.nm_bangsal',
                'penjab.png_jawab',
                'pasien.no_tlp',
                'pasien.alamat',
            ])
            ->groupBy([
                'reg_periksa.no_rawat',
                'detail_pemberian_obat.kode_brng',
                'detail_pemberian_obat.kd_bangsal',
            ]);
    }

    public static function hitungData($kd_poli, $kd_dokter, $tanggal)
    {
        return self::where('kd_poli', $kd_poli)
            ->where('kd_dokter', $kd_dokter)
            ->whereDate('tgl_registrasi', $tanggal)
            ->count();
    }
}
