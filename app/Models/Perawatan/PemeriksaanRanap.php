<?php

namespace App\Models\Perawatan;

use App\Models\Kepegawaian\Dokter;
use App\Models\Kepegawaian\Pegawai;
use App\Models\Kepegawaian\Petugas;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class PemeriksaanRanap extends Model
{
    use Sortable, Searchable;

    /**
     * The connection name for the model.
     *
     * @var ?string
     */
    protected $connection = 'mysql_sik';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pemeriksaan_ranap';

    /**
     * The primary key for the model.
     *
     * @var string|false
     */
    protected $primaryKey = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string|false
     */
    protected $keyType = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        // 
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 
    ];

    /** 
     * The columns that can be searched.
     * 
     * @var string[]
     */
    protected $searchColumns = [
        // 
    ];

    public function dpjp(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'dpjp_ranap', 'no_rawat', 'kd_dokter', 'no_rawat', 'kd_dokter');
    }

    public function scopePemeriksaanOlehFarmasi(Builder $query, string $tglAwal = '', string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $sqlSelect = <<<SQL
            pemeriksaan_ranap.tgl_perawatan,
            pemeriksaan_ranap.jam_rawat,
            pemeriksaan_ranap.no_rawat,
            pasien.nm_pasien,
            penjab.png_jawab,
            pemeriksaan_ranap.alergi,
            pemeriksaan_ranap.keluhan,
            pemeriksaan_ranap.pemeriksaan,
            pemeriksaan_ranap.penilaian,
            pemeriksaan_ranap.rtl,
            pemeriksaan_ranap.nip,
            petugas.nama,
            jabatan.nm_jbtn
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('reg_periksa', 'pemeriksaan_ranap.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('petugas', DB::raw('trim(pemeriksaan_ranap.nip)'), '=', DB::raw('trim(petugas.nip)'))
            ->leftJoin('jabatan', 'petugas.kd_jbtn', '=', 'jabatan.kd_jbtn')
            ->with('dpjp')
            ->whereIn('jabatan.kd_jbtn', ['J008', 'J015', 'J069'])
            ->whereBetween('pemeriksaan_ranap.tgl_perawatan', [$tglAwal, $tglAkhir]);
    }
}
