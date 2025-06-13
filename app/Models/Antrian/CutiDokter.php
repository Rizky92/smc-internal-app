<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;

class CutiDokter extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'cuti_dokter';

    protected $primaryKey = 'kd_dokter';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kd_dokter',
        'tanggal_awal',
        'tanggal_akhir',
    ];
}
