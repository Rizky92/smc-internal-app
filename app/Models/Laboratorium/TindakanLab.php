<?php

namespace App\Models\Laboratorium;

use App\Database\Eloquent\Model;

class TindakanLab extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_jenis_prw';

    protected $keyType = 'string';

    protected $table = 'jns_perawatan_lab';

    public $incrementing = false;

    public $timestamps = false;
}
