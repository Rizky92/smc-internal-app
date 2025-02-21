<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Pintu extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'manajemen_pintu';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function poliklinik(): BelongsToMany
    {
        $db = \DB::connection('mysql_smc')->getDatabaseName();

        return $this->belongsToMany(Poliklinik::class, "{$db}.pintu_poli", 'kd_pintu', 'kd_poli', 'kd_pintu', 'kd_poli');
    }

    public function dokter(): BelongsToMany
    {
        $db = \DB::connection('mysql_smc')->getDatabaseName();

        return $this->belongsToMany(Dokter::class, "{$db}.dokter_pintu", 'kd_pintu', 'kd_dokter', 'kd_pintu', 'kd_dokter');
    }

    public function scopeAntrianPerPintu(Builder $query, string $kd_pintu = ''): Builder
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            registrasi.no_reg,
            registrasi.no_rawat,
            dokter.nm_dokter,
            poliklinik.kd_poli,
            poliklinik.nm_poli,
            pasien.nm_pasien
            SQL;

        $registrasi = \DB::raw("{$db}.reg_periksa registrasi");
        $dokter = \DB::raw("{$db}.dokter dokter");
        $poliklinik = \DB::raw("{$db}.poliklinik poliklinik");
        $pasien = \DB::raw("{$db}.pasien pasien");
        $jadwal = \DB::raw("{$db}.jadwal jadwal");

        return $query
            ->selectRaw($sqlSelect)
            ->join('pintu_poli', 'manajemen_pintu.kd_pintu', '=', 'pintu_poli.kd_pintu')
            ->join($poliklinik, 'pintu_poli.kd_poli', '=', 'poliklinik.kd_poli')
            ->join($registrasi, function ($join) {
                $join->on('poliklinik.kd_poli', '=', 'registrasi.kd_poli')
                    ->on('pintu_poli.kd_poli', '=', 'registrasi.kd_poli');
            })
            ->join('dokter_pintu', 'manajemen_pintu.kd_pintu', '=', 'dokter_pintu.kd_pintu')
            ->join($dokter, function ($join) {
                $join->on('registrasi.kd_dokter', '=', 'dokter.kd_dokter')
                    ->on('dokter_pintu.kd_dokter', '=', 'dokter.kd_dokter');
            })
            ->join($pasien, 'registrasi.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join($jadwal, function ($join) {
                $join->on('dokter_pintu.kd_dokter', '=', 'jadwal.kd_dokter')
                    ->on('pintu_poli.kd_poli', '=', 'jadwal.kd_poli');
            })
            ->where('registrasi.tgl_registrasi', now()->toDateString())
            ->where('registrasi.stts', 'Belum')
            ->where('registrasi.status_lanjut', '!=', 'ranap')
            ->where('manajemen_pintu.kd_pintu', $kd_pintu)
            ->orderBy('jadwal.jam_mulai', 'asc')
            ->orderBy('registrasi.no_reg', 'asc')
            ->groupBy('registrasi.no_rawat');
    }

    public function scopeDokterPerPintu(Builder $query, string $kd_pintu = ''): Builder
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<'SQL'
            dokter.kd_dokter,
            dokter.nm_dokter,
            jadwal.jam_mulai,
            jadwal.jam_selesai
            SQL;

        $dokter = \DB::raw("{$db}.dokter dokter");
        $jadwal = \DB::raw("{$db}.jadwal jadwal");

        return $query
            ->selectRaw($sqlSelect)
            ->join('pintu_poli', 'manajemen_pintu.kd_pintu', '=', 'pintu_poli.kd_pintu')
            ->join('dokter_pintu', 'manajemen_pintu.kd_pintu', '=', 'dokter_pintu.kd_pintu')
            ->join($jadwal, function ($join) {
                $join->on('dokter_pintu.kd_dokter', '=', 'jadwal.kd_dokter')
                    ->on('pintu_poli.kd_poli', '=', 'jadwal.kd_poli');
            })
            ->join($dokter, 'dokter_pintu.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('jadwal.hari_kerja', strtoupper(Carbon::now()->translatedFormat('l')))
            ->where('manajemen_pintu.kd_pintu', $kd_pintu)
            ->orderBy('jadwal.jam_mulai', 'asc');
    }
}
