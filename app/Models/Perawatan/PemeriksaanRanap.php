<?php

namespace App\Models\Perawatan;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class PemeriksaanRanap extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'pemeriksaan_ranap';

    protected $primaryKey = false;

    protected $keyType = false;

    public $incrementing = false;

    public $timestamps = false;

    public function dpjp(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'dpjp_ranap', 'no_rawat', 'kd_dokter', 'no_rawat', 'kd_dokter');
    }

    public function scopePemeriksaanOlehFarmasi(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $sqlSelect = <<<'SQL'
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

        $this->addSearchConditions([
            'pemeriksaan_ranap.no_rawat',
            'pasien.no_rkm_medis',
            'pasien.nm_pasien',
            'penjab.kd_pj',
            'penjab.png_jawab',
            'ifnull(pemeriksaan_ranap.alergi, "")',
            'ifnull(pemeriksaan_ranap.keluhan, "")',
            'ifnull(pemeriksaan_ranap.pemeriksaan, "")',
            'ifnull(pemeriksaan_ranap.penilaian, "")',
            'ifnull(pemeriksaan_ranap.rtl, "")',
            'ifnull(pemeriksaan_ranap.instruksi, "")',
            'ifnull(pemeriksaan_ranap.evaluasi, "")',
            'pemeriksaan_ranap.nip',
            'petugas.nama',
            'jabatan.nm_jbtn',
        ]);

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
