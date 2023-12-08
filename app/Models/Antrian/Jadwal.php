<?php

namespace App\Models\Antrian;

use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Database\Eloquent\Model;


class Jadwal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = ['kd_dokter', 'hari_kerja', 'jam_mulai'];

    protected $keyType = 'string';

    protected $table = 'jadwal';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['kd_dokter', 'hari_kerja', 'jam_mulai', 'jam_selesai', 'kd_poli', 'kuota'];
    
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }
}
