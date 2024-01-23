<?php

namespace App\Models\Farmasi;

use App\Database\Eloquent\Model;

class ResepDokterRacikan extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'no_resep';

    protected $keyType = 'string';

    protected $table = 'resep_dokter_racikan';

    public $incrementing = false;

    public $timestamps = false;

    protected $searchColumns = [
        'no_resep',
        'no_racik',
        'nama_racik',
        'kd_racik',
        'aturan_pakai',
        'keterangan',
    ];
}
