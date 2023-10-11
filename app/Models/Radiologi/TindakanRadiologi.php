<?php

namespace App\Models\Radiologi;

use App\Database\Eloquent\Model;

class TindakanRadiologi extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    protected $table = 'jns_perawatan_radiologi';

    public $incrementing = false;

    public $timestamps = false;
}
