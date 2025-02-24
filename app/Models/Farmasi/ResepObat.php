<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResepObat extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_obat';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_resep',
        'no_rawat',
        'status',
        'kd_dokter',
    ];

    public function registrasi(): BelongsTo
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }

    public function pemberian(): HasMany
    {
        return $this->hasMany(PemberianObat::class, 'no_rawat', 'no_rawat');
    }

    public function detail(): BelongsToMany
    {
        return $this->belongsToMany(Obat::class, 'resep_dokter', 'no_resep', 'kode_brng', 'no_resep', 'kode_brng')
            ->withPivot('jml');
    }

    public function scopeJenisKunjungan(Builder $query, string $jenisKunjungan = 'semua'): Builder
    {
        $joinedTables = collect($query->toBase()->joins);

        if ($joinedTables->doesntContain(fn (JoinClause $join): bool => $join->table === 'reg_periksa')) {
            return $query;
        }

        return $query
            ->when($jenisKunjungan !== 'semua', function (Builder $query) use ($jenisKunjungan) {
                switch (Str::lower($jenisKunjungan)) {
                    case 'ralan':
                        return $query->where('resep_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
                    case 'ranap':
                        return $query->where('resep_obat.status', 'Ranap');
                    case 'igd':
                        return $query->where('resep_obat.status', 'Ralan')
                            ->where('reg_periksa.kd_poli', '=', 'IGDK');
                }
            });
    }

    public function scopeKunjunganResep(Builder $query, string $jenisResep = 'umum', string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            resep_obat.tgl_perawatan,
            concat(resep_obat.tgl_perawatan, ' ', resep_obat.jam) as waktu_validasi,
            nullif(concat(resep_obat.tgl_penyerahan, ' ', resep_obat.jam_penyerahan), '0000-00-00 00:00:00') as waktu_penyerahan,
            resep_obat.no_resep,
            pasien.no_rkm_medis,
            pasien.nm_pasien,
            penjab.png_jawab,
            resep_obat.status,
            dokter.nm_dokter,
            poliklinik.nm_poli,
            (select round(sum(detail_pemberian_obat.total)) from detail_pemberian_obat where detail_pemberian_obat.no_rawat = resep_obat.no_rawat and detail_pemberian_obat.tgl_perawatan = resep_obat.tgl_perawatan and detail_pemberian_obat.jam = resep_obat.jam) as total,
            (select count(*) from detail_pemberian_obat where detail_pemberian_obat.no_rawat = resep_obat.no_rawat and detail_pemberian_obat.tgl_perawatan = resep_obat.tgl_perawatan and detail_pemberian_obat.jam = resep_obat.jam) as jumlah
            SQL;

        $this->addSearchConditions([
            'pasien.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.png_jawab',
            'dokter.nm_dokter',
            'poliklinik.nm_poli',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['total' => 'float'])
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->where('resep_obat.tgl_perawatan', '>', '0000-00-00')
            ->when($jenisResep === 'racikan', fn ($q) => $q->whereExists(fn ($q) => $q
                ->from('detail_obat_racikan')
                ->whereColumn('detail_obat_racikan.no_rawat', 'resep_obat.no_rawat')
                ->whereColumn('detail_obat_racikan.tgl_perawatan', 'resep_obat.tgl_perawatan')
                ->whereColumn('detail_obat_racikan.jam', 'resep_obat.jam')));
    }

    public function scopePenggunaanObatPerDokter(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            resep_obat.no_resep,
            resep_obat.no_rawat,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            databarang.nama_brng,
            kategori_barang.nama,
            detail_pemberian_obat.jml,
            dokter.nm_dokter,
            case when reg_periksa.status_lanjut = 'Ranap' then (select group_concat(distinct dokter.nm_dokter separator ', ') from dpjp_ranap join dokter on dpjp_ranap.kd_dokter = dokter.kd_dokter where dpjp_ranap.no_rawat = resep_obat.no_rawat) else (select nm_dokter from dokter where kd_dokter = reg_periksa.kd_dokter) end as dpjp,
            resep_obat.status,
            poliklinik.nm_poli,
            penjab.png_jawab
            SQL;

        $this->addSearchConditions([
            'resep_obat.no_resep',
            'resep_obat.no_rawat',
            'databarang.nama_brng',
            'kategori_barang.nama',
            'dokter.kd_dokter',
            'dokter.nm_dokter',
            'resep_obat.status',
            'poliklinik.nm_poli',
            'reg_periksa.kd_pj',
            'penjab.png_jawab',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jml' => 'float'])
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('detail_pemberian_obat', 'reg_periksa.no_rawat', '=', 'detail_pemberian_obat.no_rawat')
            ->leftJoin('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('kategori_barang', 'databarang.kode_kategori', '=', 'kategori_barang.kode')
            ->whereColumn('detail_pemberian_obat.tgl_perawatan', 'resep_obat.tgl_perawatan')
            ->whereColumn('detail_pemberian_obat.jam', 'resep_obat.jam')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir]);
    }

    public function scopeKunjunganPerPoli(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endofMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            resep_obat.no_rawat,
            resep_obat.no_resep,
            pasien.nm_pasien,
            concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur) umur,
            resep_obat.tgl_perawatan,
            resep_obat.jam,
            dokter_peresep.nm_dokter nm_dokter_peresep,
            dokter_poli.nm_dokter nm_dokter_poli,
            reg_periksa.status_lanjut,
            poliklinik.nm_poli
            SQL;

        $this->addSearchConditions([
            'resep_obat.no_rawat',
            'resep_obat.no_resep',
            'pasien.nm_pasien',
            'dokter_peresep.nm_dokter',
            'dokter_poli.nm_dokter',
            'reg_periksa.status_lanjut',
            'poliklinik.nm_poli',
        ]);

        $this->addRawColumns([
            'umur'              => DB::raw("concat(reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur)"),
            'nm_dokter_peresep' => 'dokter_peresep.nm_dokter',
            'nm_dokter_poli'    => 'dokter_poli.nm_dokter',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->leftJoin('dokter as dokter_poli', 'reg_periksa.kd_dokter', '=', 'dokter_poli.kd_dokter')
            ->leftJoin('dokter as dokter_peresep', 'resep_obat.kd_dokter', '=', 'dokter_peresep.kd_dokter')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir]);
    }

    public function scopeKunjunganPasien(Builder $query, string $jenisPerawatan = 'semua', string $year = '2022'): Builder
    {
        $sqlSelect = <<<'SQL'
            count(resep_obat.no_resep) jumlah,
            month(resep_obat.tgl_perawatan) bulan
            SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah' => 'int', 'bulan' => 'int'])
            ->leftJoin('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('resep_obat.tgl_perawatan', ["{$year}-01-01", "{$year}-12-31"])
            ->jenisKunjungan($jenisPerawatan)
            ->groupByRaw('month(resep_obat.tgl_perawatan)');
    }

    public function scopeRincianKunjunganRalan(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endofMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
            resep_obat.tgl_perawatan,
            resep_obat.no_resep,
            resep_obat.no_rawat,
            pasien.nm_pasien,
            penjab.png_jawab,
            resep_obat.status,
            dokter.nm_dokter,
            databarang.kode_brng, 
            databarang.nama_brng,
            detail_pemberian_obat.biaya_obat,
            detail_pemberian_obat.jml,
            detail_pemberian_obat.total,
            (select round(sum(detail_pemberian_obat.total)) from detail_pemberian_obat where detail_pemberian_obat.no_rawat = resep_obat.no_rawat and detail_pemberian_obat.tgl_perawatan = resep_obat.tgl_perawatan and detail_pemberian_obat.jam = resep_obat.jam) as total_harga 
            SQL;

        $this->addSearchConditions([
            'resep_obat.no_resep',
            'resep_obat.no_rawat',
            'dokter.nm_dokter',
            'databarang.nama_brng',
            'resep_obat.status',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['total' => 'float', 'biaya_obat' => 'float', 'total_harga' => 'float'])
            ->join('reg_periksa', 'resep_obat.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('detail_pemberian_obat', fn (JoinClause $join) => $join
                ->on('resep_obat.no_rawat', '=', 'detail_pemberian_obat.no_rawat')
                ->on('resep_obat.tgl_perawatan', '=', 'detail_pemberian_obat.tgl_perawatan')
                ->on('resep_obat.jam', '=', 'detail_pemberian_obat.jam'))
            ->join('databarang', 'detail_pemberian_obat.kode_brng', '=', 'databarang.kode_brng')
            ->whereBetween('resep_obat.tgl_perawatan', [$tglAwal, $tglAkhir])
            ->where('resep_obat.status', 'ralan')
            ->where('tgl_peresepan', '>', '0000-00-00')
            ->where('reg_periksa.kd_poli', '!=', 'IGDK');
    }

    public static function kunjunganPasienRalan(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('ralan', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function kunjunganPasienRanap(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('ranap', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }

    public static function kunjunganPasienIGD(string $year = '2022'): array
    {
        $data = static::kunjunganPasien('IGD', $year)->pluck('jumlah', 'bulan');

        return map_bulan($data);
    }
}
