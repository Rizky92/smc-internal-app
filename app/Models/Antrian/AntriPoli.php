<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;
use App\Models\Kepegawaian\Dokter;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;

class AntriPoli extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'antripoli';

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli', 'kd_poli');
    }

    public function registrasi()
    {
        return $this->belongsTo(RegistrasiPasien::class, 'no_rawat', 'no_rawat');
    }
}
