<?php

namespace App\Models\Aplikasi;

use App\Database\Eloquent\Model;
use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function scopeAntrianPerPintu(Builder $query, string $kd_pintu=""): Builder
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<SQL
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

        return $query
            ->selectRaw($sqlSelect)
            ->join('pintu_poli', 'manajemen_pintu.kd_pintu', '=', 'pintu_poli.kd_pintu')
            ->join($poliklinik, 'pintu_poli.kd_poli', '=', 'poliklinik.kd_poli')
            ->join($registrasi, 'poliklinik.kd_poli', '=', 'registrasi.kd_poli')
            ->join($dokter, 'registrasi.kd_dokter', '=', 'dokter.kd_dokter')
            ->join($pasien, 'registrasi.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('registrasi.tgl_registrasi', now()->format('Y-m-d'))
            ->where('registrasi.stts', 'Belum')
            ->where('manajemen_pintu.kd_pintu', $kd_pintu);
    }
}
