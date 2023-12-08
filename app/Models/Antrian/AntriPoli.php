<?php

namespace App\Models\Antrian;

use App\Database\Eloquent\Model;

class AntriPoli extends Model
{
    
    protected $connection = 'mysql_sik';

    protected $table = 'antripoli';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kd_poli',
        'kd_dokter',
        'no_rawat',
        'status',
    ];

}
