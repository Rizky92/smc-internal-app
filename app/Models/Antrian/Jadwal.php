<?php

namespace App\Models\Antrian;

use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
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

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function poliklinik()
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
            jadwal.jam_selesai,
            jadwal.kuota,
            (SELECT
                CASE
                    WHEN COUNT(*) > jadwal.kuota THEN jadwal.kuota
                    ELSE COUNT(*)
                END   
            FROM reg_periksa 
            WHERE kd_poli = poliklinik.kd_poli 
            AND kd_dokter = jadwal.kd_dokter 
            AND tgl_registrasi = CURDATE()
            ) AS total_registrasi
        SQL;
    
        $this->addSearchConditions([
            'dokter.nm_dokter',
            'poliklinik.nm_poli',
        ]);
    
        $dayOfWeekMap = [
            'Sunday' => 'AHAD',
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
            'Saturday' => 'SABTU',
        ];
    
        return $query
            ->selectRaw($sqlSelect)
            ->join('dokter', 'jadwal.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('poliklinik', 'jadwal.kd_poli', '=', 'poliklinik.kd_poli')
            ->where('jadwal.hari_kerja', '=', strtoupper($dayOfWeekMap[date('l')]))
            ->orderByRaw("CASE WHEN poliklinik.nm_poli = 'Poli Eksekutif' THEN 1 ELSE 0 END, poliklinik.nm_poli");
    }    
    
}
