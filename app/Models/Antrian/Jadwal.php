<?php

namespace App\Models\Antrian;

use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Jadwal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = ['kd_dokter', 'hari_kerja', 'jam_mulai'];

    protected $keyType = 'string';

    protected $table = 'jadwal';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Relations\BelongsTo<Dokter>
     */
    public function dokter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Relations\BelongsTo<Poliklinik>
     */
    public function poliklinik(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function scopeJadwalDokter(Builder $query): Builder
    {
        $sqlSelect = <<<SQL
        dokter.kd_dokter,
        dokter.nm_dokter,
        poliklinik.kd_poli, 
        poliklinik.nm_poli,
        jadwal.hari_kerja,
        jadwal.jam_mulai, 
        jadwal.jam_selesai
        SQL;

        $this->addSearchConditions([
            'dokter.nm_dokter',
            'poliklinik.nm_poli',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('dokter', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->leftJoin('poliklinik', 'jadwal.kd_poli', '=', 'poliklinik.kd_poli');
    }
}
