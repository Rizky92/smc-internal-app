<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;

class Pintu extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'pintu_smc';

    protected $primaryKey = 'kd_pintu';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function poliklinik(): BelongsToMany
    {
        return $this->belongsToMany(Poliklinik::class, 'set_pintu_smc', 'kd_pintu', 'kd_poli');
    }

    public function dokter(): BelongsToMany
    {
        return $this->belongsToMany(Dokter::class, 'set_pintu_smc', 'kd_pintu', 'kd_dokter');
    }

    public function scopeAntreanPerPintu(Builder $query, string $kd_pintu = '', string $condition = ''): Builder
    {
        $sqlSelect = <<<SQL
            reg_periksa.no_reg,
            reg_periksa.no_rawat,
            dokter.kd_dokter,
            dokter.nm_dokter,
            poliklinik.kd_poli,
            poliklinik.nm_poli,
            pasien.nm_pasien,
            pintu_smc.kd_pintu,
            pintu_smc.nm_pintu,
            antripintu_smc.status,
            antripintu_smc.waktu_panggil
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('set_pintu_smc', 'pintu_smc.kd_pintu', '=', 'set_pintu_smc.kd_pintu')
            ->join('poliklinik', 'set_pintu_smc.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'set_pintu_smc.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('reg_periksa', function ($join) {
                $join->on('reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
                    ->on('reg_periksa.kd_poli', '=', 'poliklinik.kd_poli');
            })
            ->join('jadwal', function ($join) {
                $join->on('set_pintu_smc.kd_dokter', '=', 'jadwal.kd_dokter')
                    ->on('set_pintu_smc.kd_poli', '=', 'jadwal.kd_poli');
            })
            ->leftJoin('antripintu_smc', fn (JoinClause $join) => $join
                ->on('reg_periksa.no_rawat', '=', 'antripintu_smc.no_rawat')
                ->on('pintu_smc.kd_pintu', '=', 'antripintu_smc.kd_pintu')
            )
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('reg_periksa.tgl_registrasi', now()->toDateString())
            ->where('reg_periksa.status_lanjut', '!=', 'ranap')
            ->where('pintu_smc.kd_pintu', $kd_pintu)
            ->when($condition === 'list', fn (Builder $q) => $q->whereIn('reg_periksa.stts', ['Belum', 'TTV']))
            ->groupBy('reg_periksa.no_rawat');
    }

    public function scopeDokterPerPintu(Builder $query, string $kd_pintu = ''): Builder
    {
        $sqlSelect = <<<SQL
            dokter.kd_dokter,
            dokter.nm_dokter,
            jadwal.jam_mulai,
            jadwal.jam_selesai
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join('set_pintu_smc', 'pintu_smc.kd_pintu', '=', 'set_pintu_smc.kd_pintu')
            ->join('dokter', 'set_pintu_smc.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('jadwal', function ($join) {
                $join->on('set_pintu_smc.kd_dokter', '=', 'jadwal.kd_dokter')
                    ->on('set_pintu_smc.kd_poli', '=', 'jadwal.kd_poli');
            })
            ->where('jadwal.hari_kerja', strtoupper(Carbon::now()->translatedFormat('l')))
            ->where('pintu_smc.kd_pintu', $kd_pintu)
            ->orderBy('jadwal.jam_mulai', 'asc');
    }
}
